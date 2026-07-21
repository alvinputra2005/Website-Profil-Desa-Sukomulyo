document.addEventListener('DOMContentLoaded',()=>{
  if(window.jQuery){const $=window.jQuery;
    const saved=localStorage.getItem('sidebar'); if(saved==='collapsed') document.body.classList.add('sidebar-collapse');
    $('.sidebar-toggle').on('click',()=>setTimeout(()=>localStorage.setItem('sidebar',document.body.classList.contains('sidebar-collapse')?'collapsed':'expanded'),0));
    $('#cari-menu').on('input',function(){const term=this.value.toLowerCase().trim();$('.sidebar-menu>li:not(.header)').each(function(){const $item=$(this),matches=$item.text().toLowerCase().includes(term);$item.toggle(matches);if(term&&matches)$item.addClass('menu-open').children('.treeview-menu').show();else if(!term&&!$item.hasClass('active'))$item.removeClass('menu-open').children('.treeview-menu').hide()})});
    $('.select2').select2({width:'100%'}); $('[data-toggle="tooltip"]').tooltip();
    setTimeout(()=>$('#notifikasi').fadeTo(500,0).slideUp(500),5000);
  }
  document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault();const message=form.dataset.confirm||'Yakin ingin melanjutkan?';if(window.Swal)Swal.fire({title:'Konfirmasi',text:message,icon:'warning',showCancelButton:true,confirmButtonColor:'#526b42',cancelButtonColor:'#6f7870',confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Batal'}).then(r=>{if(r.isConfirmed)form.submit()});else if(confirm(message))form.submit()}));
  document.querySelectorAll('[data-dirty-form]').forEach(form=>{let dirty=false;form.addEventListener('input',()=>dirty=true);form.addEventListener('submit',()=>dirty=false);window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue=''}})});
  document.querySelectorAll('[data-editor]').forEach(editor=>{const area=editor.querySelector('textarea'),surface=editor.querySelector('[contenteditable]');if(!area||!surface)return;surface.innerHTML=area.value;surface.addEventListener('input',()=>area.value=surface.innerHTML);editor.querySelectorAll('[data-command]').forEach(btn=>btn.addEventListener('click',()=>{document.execCommand(btn.dataset.command,false,null);surface.focus();area.value=surface.innerHTML}))});
});
