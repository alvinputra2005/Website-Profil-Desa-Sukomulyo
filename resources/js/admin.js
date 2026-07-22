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
  document.querySelectorAll('[data-article-editor]').forEach(editor=>{
    const area=editor.querySelector('textarea'),surface=editor.querySelector('[contenteditable]'),imageInput=editor.querySelector('[data-image-input]'),status=editor.querySelector('[data-editor-status]');
    if(!area||!surface)return;
    let savedRange=null,selectedFigure=null;
    const sync=()=>{area.value=surface.innerHTML};
    const rememberSelection=()=>{const selection=window.getSelection();if(selection?.rangeCount&&surface.contains(selection.anchorNode))savedRange=selection.getRangeAt(0).cloneRange()};
    const restoreSelection=()=>{surface.focus();if(savedRange){const selection=window.getSelection();selection.removeAllRanges();selection.addRange(savedRange)}};
    const showStatus=(message,isError=false)=>{if(!status)return;status.hidden=false;status.textContent=message;status.classList.toggle('is-error',isError)};
    surface.querySelectorAll('img').forEach(image=>{try{const url=new URL(image.src);if((url.hostname==='localhost'||url.hostname==='127.0.0.1')&&url.pathname.startsWith('/storage/'))image.src=url.pathname}catch(error){}});
    sync();
    surface.addEventListener('input',()=>{rememberSelection();sync()});
    surface.addEventListener('keyup',rememberSelection);
    surface.addEventListener('mouseup',event=>{rememberSelection();selectedFigure=event.target.closest('figure.article-image')});
    editor.querySelectorAll('[data-command]').forEach(button=>button.addEventListener('mousedown',event=>event.preventDefault()));
    editor.querySelectorAll('[data-command]').forEach(button=>button.addEventListener('click',()=>{restoreSelection();document.execCommand(button.dataset.command,false,button.dataset.value||null);rememberSelection();sync()}));
    editor.querySelector('[data-action="link"]')?.addEventListener('click',()=>{rememberSelection();const url=window.prompt('Masukkan alamat tautan (https://...)');if(url){restoreSelection();document.execCommand('createLink',false,url);sync()}surface.focus()});
    editor.querySelector('[data-action="image"]')?.addEventListener('click',()=>{rememberSelection();imageInput?.click()});
    editor.querySelectorAll('[data-action="image-align"]').forEach(button=>button.addEventListener('click',()=>{if(!selectedFigure){showStatus('Klik gambar di dalam editor terlebih dahulu.',true);return}selectedFigure.className=`article-image align-${button.dataset.align}`;sync();showStatus('Posisi gambar diperbarui.')}));
    imageInput?.addEventListener('change',async()=>{const file=imageInput.files?.[0];if(!file)return;showStatus('Mengunggah gambar...');const data=new FormData();data.append('image',file);try{const response=await fetch(editor.dataset.uploadUrl,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json'},body:data});const result=await response.json();if(!response.ok)throw new Error(result.message||Object.values(result.errors||{}).flat()[0]||'Gambar gagal diunggah.');restoreSelection();const figure=document.createElement('figure');figure.className='article-image align-center';const image=document.createElement('img');image.src=result.url;image.alt=result.alt||file.name;const caption=document.createElement('figcaption');caption.textContent='Klik untuk menulis keterangan gambar';figure.append(image,caption);const range=window.getSelection()?.rangeCount?window.getSelection().getRangeAt(0):null;if(range){range.deleteContents();range.insertNode(figure);range.setStartAfter(figure);range.collapse(true);const selection=window.getSelection();selection.removeAllRanges();selection.addRange(range);savedRange=range.cloneRange()}else surface.append(figure);selectedFigure=figure;sync();showStatus('Gambar berhasil disisipkan. Klik gambar untuk mengatur posisinya.')}catch(error){showStatus(error.message||'Gambar gagal diunggah.',true)}finally{imageInput.value=''}});
    editor.closest('form')?.addEventListener('submit',sync);
  });
  const title=document.querySelector('#title'),titleCount=document.querySelector('#title-count');if(title&&titleCount){const count=()=>titleCount.textContent=title.value.length;title.addEventListener('input',count);count()}
});
