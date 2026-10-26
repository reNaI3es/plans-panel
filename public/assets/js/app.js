/** reNaI3es — client JS (≤150 рядків) */
(function(){
  /* Parallax для 3D-фігур */
  var tx=0,ty=0,cx=0,cy=0,sh=document.querySelectorAll('.sh[data-d]');
  if(sh.length){
    addEventListener('pointermove',function(e){tx=e.clientX/innerWidth-.5;ty=e.clientY/innerHeight-.5});
    (function loop(){cx+=(tx-cx)*.06;cy+=(ty-cy)*.06;
      sh.forEach(function(s){var d=+s.dataset.d;s.style.transform='translate3d('+(cx*d).toFixed(2)+'px,'+(cy*d).toFixed(2)+'px,0)'});
      requestAnimationFrame(loop)})();
  }
  /* Kanban drag-and-drop */
  var board=document.querySelector('.kanban-board');
  if(board){
    var dragged=null;
    board.querySelectorAll('.kanban-card').forEach(function(card){
      card.setAttribute('draggable','true');
      card.addEventListener('dragstart',function(e){dragged=card;card.classList.add('dragging');e.dataTransfer.setData('text/plain',card.dataset.id)});
      card.addEventListener('dragend',function(){card.classList.remove('dragging');dragged=null});
    });
    board.querySelectorAll('.kanban-column').forEach(function(col){
      col.addEventListener('dragover',function(e){e.preventDefault()});
      col.addEventListener('drop',function(e){
        e.preventDefault();if(!dragged)return;
        (col.querySelector('.kanban-list')||col).appendChild(dragged);
        var taskId=dragged.dataset.id,newStatus=col.dataset.status;
        if(taskId&&newStatus){
          var csrf=document.querySelector('meta[name="csrf-token"]');
          var form=document.createElement('form');form.method='POST';
          form.action=dragged.dataset.statusUrl||('/tasks/'+taskId+'/status');
          form.innerHTML='<input type="hidden" name="_csrf" value="'+(csrf?csrf.content:'')+'"><input type="hidden" name="status" value="'+newStatus+'">';
          document.body.appendChild(form);form.submit();
        }
      });
    });
  }
  /* Flash auto-hide */
  document.querySelectorAll('.flash').forEach(function(el){
    setTimeout(function(){el.style.transition='opacity 300ms';el.style.opacity='0';setTimeout(function(){el.remove()},300)},5000);
  });
  /* Confirm */
  document.querySelectorAll('[data-confirm]').forEach(function(el){
    el.addEventListener('click',function(e){if(!confirm(el.dataset.confirm||'Ви впевнені?'))e.preventDefault()});
  });
  /* Search hotkey */
  var q=document.getElementById('globalSearch');
  if(q)addEventListener('keydown',function(e){if(e.key==='/'&&document.activeElement!==q){e.preventDefault();q.focus()}});
})();
