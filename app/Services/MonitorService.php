<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\{Db, Config, Crypto};

/**
 * Моніторинг серверів: Source (A2S_INFO), Minecraft (SLP), HTTP-ping.
 */
class MonitorService
{
    /**
     * Перевірити всі сервери.
     */
    public function checkAll(): array
    {
        $db = Db::instance();
        $servers = $db->fetchAll("SELECT * FROM servers WHERE monitor_enabled = 1");
        $results = [];
        foreach ($servers as $server) {
            $result = $this->checkServer($server);
            $this->saveStat($server['id'], $result);
            $this->handleIncidents($server, $result);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * Перевірити один сервер.
     */
    public function checkServer(array $server): array
    {
        $result = [
            'server_id' => $server['id'],
            'is_online' => false,
            'players_online' => null,
            'players_max' => null,
            'map_name' => null,
            'version' => null,
            'ping_ms' => null,
            'error_text' => null,
        ];

        $start = microtime(true);
        try {
            $result = match ($server['protocol']) {
                'source' => $this->querySource($server),
                'minecraft' => $this->queryMinecraft($server),
                'http' => $this->queryHttp($server),
                default => $result,
            };
            $result['ping_ms'] = (int)((microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $result['error_text'] = mb_substr($e->getMessage(), 0, 255);
        }
        $result['server_id'] = $server['id'];
        return $result;
    }

    /**
     * Source/A2S_INFO query (спрощена реалізація).
     */
    private function querySource(array $server): array
    {
        $host = $server['host'];
        $port = $server['query_port'] ?? $server['port'];

        if (!function_exists('socket_create')) {
            return $this->fallbackPing($host, (int)$port);
        }

        $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) return $this->fallbackPing($host, (int)$port);

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 3, 'usec' => 0]);

        // A2S_INFO request
        $challenge = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";
        @socket_sendto($socket, $challenge, strlen($challenge), 0, $host, (int)$port);

        $response = '';
        $from = '';
        $fromPort = 0;
        $bytes = @socket_recvfrom($socket, $response, 1400, 0, $from, $fromPort);
        @socket_close($socket);

        if (!$bytes || $bytes < 5) {
            return $this->fallbackPing($host, (int)$port);
        }

        // Парсинг A2S_INFO response
        $data = substr($response, 5); // skip header
        $result = ['is_online' => true, 'players_online' => null, 'players_max' => null, 'map_name' => null, 'version' => null, 'ping_ms' => null];

        // Пропускаем protocol, name, map, folder (null-terminated strings)
        $offset = 0;
        for ($i = 0; $i < 4; $i++) {
            $pos = strpos($data, "\x00", $offset);
            if ($pos === false) return $result;
            $offset = $pos + 1;
        }

        // Players
        if (strlen($data) > $offset) {
            $result['players_online'] = ord($data[$offset]);
            $offset++;
        }
        if (strlen($data) > $offset) {
            $result['players_max'] = ord($data[$offset]);
        }

        return $result;
    }

    /**
     * Minecraft Server List Ping (SLP 1.7+).
     */
    private function queryMinecraft(array $server): array
    {
        $host = $server['host'];
        $port = (int)($server['query_port'] ?? $server['port']);
        $result = ['is_online' => false, 'players_online' => null, 'players_max' => null, 'map_name' => null, 'version' => null, 'ping_ms' => null];

        // Упрощённый ping через fsockopen
        $fp = @fsockopen($host, $port, $errno, $errstr, 3);
        if (!$fp) return $result;

        // Handshake packet
        $data = "\x00"; // packet ID
        $data .= "\x04"; // protocol version
        $data .= $this->writeVarInt(strlen($host)) . $host;
        $data .= pack('n', $port);
        $data .= $this->writeVarInt(1); // next state: status

        $packet = $this->writeVarInt(strlen($data)) . $data;
        @fwrite($fp, $packet);

        // Status request
        $statusReq = "\x00";
        $statusPacket = $this->writeVarInt(strlen($statusReq)) . $statusReq;
        @fwrite($fp, $statusPacket);

        $response = @fread($fp, 4096);
        @fclose($fp);

        if (!$response) return $result;

        // Пытаемся извлечь JSON из ответа
        $jsonStart = strpos($response, '{');
        if ($jsonStart !== false) {
            $json = substr($response, $jsonStart);
            $data = json_decode($json, true);
            if ($data) {
                $result['is_online'] = true;
                $result['players_online'] = $data['players']['online'] ?? null;
                $result['players_max'] = $data['players']['max'] ?? null;
                $result['version'] = $data['version']['name'] ?? null;
            }
        }

        return $result;
    }

    /**
     * HTTP-ping (для веб-серверов).
     */
    private function queryHttp(array $server): array
    {
        $url = 'http://' . $server['host'] . ':' . $server['port'] . '/';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'is_online' => $code > 0 && $code < 500,
            'players_online' => null,
            'players_max' => null,
            'map_name' => null,
            'version' => null,
            'ping_ms' => null,
        ];
    }

    /**
     * Fallback ping через fsockopen.
     */
    private function fallbackPing(string $host, int $port): array
    {
        $start = microtime(true);
        $fp = @fsockopen($host, $port, $errno, $errstr, 3);
        $ping = (int)((microtime(true) - $start) * 1000);
        if (!$fp) {
            return ['is_online' => false, 'players_online' => null, 'players_max' => null, 'map_name' => null, 'version' => null, 'ping_ms' => $ping];
        }
        @fclose($fp);
        return ['is_online' => true, 'players_online' => null, 'players_max' => null, 'map_name' => null, 'version' => null, 'ping_ms' => $ping];
    }

    private function saveStat(int $serverId, array $result): void
    {
        $db = Db::instance();
        $db->execute(
            "INSERT INTO server_stats (server_id, is_online, players_online, players_max, map_name, version, ping_ms, error_text, checked_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$serverId, (int)$result['is_online'], $result['players_online'], $result['players_max'], $result['map_name'], $result['version'], $result['ping_ms'], $result['error_text']]
        );
        $db->execute(
            "UPDATE servers SET last_status = ?, last_checked_at = NOW() WHERE id = ?",
            [$result['is_online'] ? 'online' : 'offline', $serverId]
        );
    }

    private function handleIncidents(array $server, array $result): void
    {
        $db = Db::instance();
        $lastStatus = $server['last_status'];
        $newStatus = $result['is_online'] ? 'online' : 'offline';

        if ($lastStatus === 'online' && $newStatus === 'offline') {
            // Сервер упал — создаём инцидент
            $db->execute(
                "INSERT INTO server_incidents (server_id, kind, message, started_at) VALUES (?, 'down', ?, NOW())",
                [$server['id'], $result['error_text'] ?? 'Server offline']
            );
        } elseif ($lastStatus === 'offline' && $newStatus === 'online') {
            // Сервер поднялся — закрываем инцидент
            $db->execute(
                "UPDATE server_incidents SET ended_at = NOW() WHERE server_id = ? AND ended_at IS NULL",
                [$server['id']]
            );
        }
    }

    private function writeVarInt(int $value): string
    {
        $out = '';
        do {
            $byte = $value & 0x7F;
            $value >>= 7;
            if ($value) $byte |= 0x80;
            $out .= chr($byte);
        } while ($value);
        return $out;
    }
}
