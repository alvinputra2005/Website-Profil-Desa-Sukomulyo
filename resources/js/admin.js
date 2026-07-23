import { initAjaxNavigation } from './ajax';

const initAdminPage = () => {
  if(window.jQuery){const $=window.jQuery;
    const saved=localStorage.getItem('sidebar'); if(saved==='collapsed') document.body.classList.add('sidebar-collapse');
    $('.sidebar-toggle').on('click',()=>setTimeout(()=>localStorage.setItem('sidebar',document.body.classList.contains('sidebar-collapse')?'collapsed':'expanded'),0));
    $('#cari-menu').on('input',function(){const term=this.value.toLowerCase().trim();$('.sidebar-menu>li:not(.header)').each(function(){const $item=$(this),matches=$item.text().toLowerCase().includes(term);$item.toggle(matches);if(term&&matches)$item.addClass('menu-open').children('.treeview-menu').show();else if(!term&&!$item.hasClass('active'))$item.removeClass('menu-open').children('.treeview-menu').hide()})});
    $('.select2').select2({width:'100%'}); $('[data-toggle="tooltip"]').tooltip();
    if($.fn.tree)$('[data-widget="tree"]').tree();
    if($.fn.boxWidget)$('[data-widget="collapse"]').closest('.box').boxWidget();
    if($.fn.layout&&$('body').data('lte.layout'))$('body').layout('fix');
    setTimeout(()=>$('#notifikasi').fadeTo(500,0).slideUp(500),5000);
  }
  document.querySelectorAll('[data-dirty-form]').forEach(form=>{
    if(form.dataset.dirtyBound)return;
    form.dataset.dirtyBound='true';
    form.addEventListener('input',()=>{form.dataset.dirty='true'});
    form.addEventListener('submit',()=>{delete form.dataset.dirty});
  });
  if(!window.__dirtyFormWarningBound){
    window.__dirtyFormWarningBound=true;
    window.addEventListener('beforeunload',event=>{
      if(document.querySelector('[data-dirty-form][data-dirty="true"]')){
        event.preventDefault();
        event.returnValue='';
      }
    });
  }
  document.querySelectorAll('[data-image-picker]').forEach(picker=>{
    if(picker.dataset.imagePickerBound)return;
    picker.dataset.imagePickerBound='true';
    const select=picker.querySelector('[data-image-select]'),upload=picker.querySelector('[data-image-upload]'),preview=picker.querySelector('[data-image-preview]'),alt=picker.querySelector('[data-image-alt]'),remove=picker.querySelector('[data-image-remove]');
    const empty=()=>{preview.innerHTML='<i class="fa fa-picture-o" aria-hidden="true"></i><span>Belum ada gambar</span>'};
    const show=url=>{
      if(!url){empty();return}
      preview.innerHTML='';
      const image=document.createElement('img');
      image.src=url;
      image.alt=alt.value;
      preview.appendChild(image);
    };
    select?.addEventListener('change',()=>{
      const option=select.options[select.selectedIndex];
      remove.value='0';
      upload.value='';
      if(option?.dataset.url){show(option.dataset.url);alt.value=option.dataset.alt||''}else empty();
    });
    upload?.addEventListener('change',()=>{
      const file=upload.files?.[0];
      if(!file)return;
      select.value='';
      if(window.jQuery)window.jQuery(select).trigger('change.select2');
      remove.value='0';
      show(URL.createObjectURL(file));
      if(!alt.value)alt.value=file.name.replace(/\.[^/.]+$/,'');
    });
    alt?.addEventListener('input',()=>{
      const image=preview.querySelector('img');
      if(image)image.alt=alt.value;
    });
    picker.querySelector('[data-image-clear]')?.addEventListener('click',()=>{
      select.value='';
      if(window.jQuery)window.jQuery(select).trigger('change.select2');
      upload.value='';
      alt.value='';
      remove.value='1';
      empty();
    });
  });
  const officialForm=document.getElementById('official-form');
  if(officialForm&&!officialForm.dataset.officialBound){
    officialForm.dataset.officialBound='true';
    const sourceInputs=Array.from(officialForm.querySelectorAll('input[name="source"]')),residentPicker=officialForm.querySelector('[data-resident-picker]'),residentSelect=document.getElementById('resident_id'),residentFields=Array.from(officialForm.querySelectorAll('[data-resident-value]')),preview=officialForm.querySelector('[data-full-name-preview]'),color=document.getElementById('organization_color'),colorPreview=officialForm.querySelector('.official-color-preview'),camera=document.getElementById('photo_camera'),imagePreview=officialForm.querySelector('[data-image-preview]');
    const currentSource=()=>sourceInputs.find(input=>input.checked)?.value||'external';
    const updateFullName=()=>{
      const prefix=document.getElementById('title_prefix')?.value.trim()||'',name=document.getElementById('name')?.value.trim()||'',suffix=document.getElementById('title_suffix')?.value.trim()||'';
      if(preview)preview.value=[prefix,name].filter(Boolean).join(' ')+(suffix?', '+suffix:'');
    };
    const populateResident=()=>{
      if(currentSource()!=='resident'||!residentSelect)return;
      const option=residentSelect.options[residentSelect.selectedIndex];
      if(!option?.value)return;
      residentFields.forEach(field=>{const key=field.dataset.residentValue;field.value=option.dataset[key]||'';if(window.jQuery&&field.matches('select'))window.jQuery(field).trigger('change.select2')});
      updateFullName();
    };
    const updateSource=()=>{
      const fromResident=currentSource()==='resident';
      if(residentPicker)residentPicker.hidden=!fromResident;
      if(residentSelect)residentSelect.required=fromResident;
      residentFields.forEach(field=>{field.readOnly=fromResident&&!field.matches('select');field.classList.toggle('official-readonly',fromResident)});
      if(fromResident)populateResident();
    };
    sourceInputs.forEach(input=>input.addEventListener('change',updateSource));
    residentSelect?.addEventListener('change',populateResident);
    if(window.jQuery&&residentSelect)window.jQuery(residentSelect).on('select2:select select2:clear',populateResident);
    ['name','title_prefix','title_suffix'].forEach(id=>document.getElementById(id)?.addEventListener('input',updateFullName));
    color?.addEventListener('input',()=>{if(/^#[0-9A-Fa-f]{6}$/.test(color.value)&&colorPreview)colorPreview.style.background=color.value});
    camera?.addEventListener('change',()=>{
      const file=camera.files?.[0];
      if(!file||!imagePreview)return;
      imagePreview.innerHTML='';
      const image=document.createElement('img');
      image.src=URL.createObjectURL(file);
      image.alt='Pratinjau foto dari kamera';
      imagePreview.appendChild(image);
      const remove=officialForm.querySelector('[data-image-remove]');
      if(remove)remove.value='0';
    });
    updateSource();
    updateFullName();
  }
  const officialCheckAll=document.querySelector('[data-check-all-officials]');
  if(officialCheckAll&&!officialCheckAll.dataset.bulkBound){
    officialCheckAll.dataset.bulkBound='true';
    const checks=Array.from(document.querySelectorAll('[data-official-check]')),bulkButton=document.querySelector('[data-bulk-delete]');
    const update=()=>{
      const checked=checks.filter(checkbox=>checkbox.checked).length;
      if(bulkButton)bulkButton.disabled=checked===0;
      officialCheckAll.checked=checks.length>0&&checked===checks.length;
      officialCheckAll.indeterminate=checked>0&&checked<checks.length;
    };
    officialCheckAll.addEventListener('change',()=>{checks.forEach(checkbox=>{checkbox.checked=officialCheckAll.checked});update()});
    checks.forEach(checkbox=>checkbox.addEventListener('change',update));
    update();
  }
  document.querySelectorAll('[data-article-editor]').forEach(editor=>{
    const area=editor.querySelector('textarea'),surface=editor.querySelector('[contenteditable]'),imageInput=editor.querySelector('[data-image-input]'),status=editor.querySelector('[data-editor-status]');
    if(!area||!surface)return;
    let savedRange=null,selectedFigure=null;
    const sync=()=>{const clean=surface.cloneNode(true);clean.querySelectorAll('[data-resize-handle],[data-resize-label]').forEach(node=>node.remove());clean.querySelectorAll('.is-selected,.is-resizing').forEach(node=>node.classList.remove('is-selected','is-resizing'));area.value=clean.innerHTML};
    const rememberSelection=()=>{const selection=window.getSelection();if(selection?.rangeCount&&surface.contains(selection.anchorNode))savedRange=selection.getRangeAt(0).cloneRange()};
    const restoreSelection=()=>{surface.focus();if(savedRange){const selection=window.getSelection();selection.removeAllRanges();selection.addRange(savedRange)}};
    const showStatus=(message,isError=false)=>{if(!status)return;status.hidden=false;status.textContent=message;status.classList.toggle('is-error',isError)};
    const decorateFigure=figure=>{if(figure.querySelector('[data-resize-handle]'))return;figure.dataset.width=figure.dataset.width||'70';const label=document.createElement('span');label.dataset.resizeLabel='';label.contentEditable='false';label.textContent=`${figure.dataset.width}%`;const handle=document.createElement('span');handle.dataset.resizeHandle='';handle.contentEditable='false';handle.title='Tarik untuk mengubah ukuran gambar';figure.append(label,handle);handle.addEventListener('mousedown',event=>{event.preventDefault();event.stopPropagation();selectedFigure=figure;if(figure.classList.contains('align-full')){figure.classList.remove('align-full');figure.classList.add('align-center')}const startX=event.clientX,startWidth=Number(figure.dataset.width||70),editorWidth=surface.getBoundingClientRect().width;figure.classList.add('is-resizing','is-selected');const move=moveEvent=>{const direction=figure.classList.contains('align-right')?-1:1;const change=((moveEvent.clientX-startX)*direction/editorWidth)*100;const width=Math.max(20,Math.min(100,Math.round((startWidth+change)/5)*5));figure.dataset.width=String(width);label.textContent=`${width}%`};const stop=()=>{document.removeEventListener('mousemove',move);document.removeEventListener('mouseup',stop);figure.classList.remove('is-resizing');sync();showStatus(`Ukuran gambar diubah menjadi ${figure.dataset.width}%.`)};document.addEventListener('mousemove',move);document.addEventListener('mouseup',stop)})};
    surface.querySelectorAll('img').forEach(image=>{try{const url=new URL(image.src);if((url.hostname==='localhost'||url.hostname==='127.0.0.1')&&url.pathname.startsWith('/storage/'))image.src=url.pathname}catch(error){}});
    surface.querySelectorAll('figure.article-image').forEach(decorateFigure);
    sync();
    surface.addEventListener('input',()=>{rememberSelection();sync()});
    surface.addEventListener('keyup',rememberSelection);
    surface.addEventListener('mouseup',event=>{rememberSelection();selectedFigure=event.target.closest('figure.article-image');surface.querySelectorAll('figure.article-image').forEach(figure=>figure.classList.toggle('is-selected',figure===selectedFigure))});
    editor.querySelectorAll('[data-command]').forEach(button=>button.addEventListener('mousedown',event=>event.preventDefault()));
    editor.querySelectorAll('[data-command]').forEach(button=>button.addEventListener('click',()=>{restoreSelection();document.execCommand(button.dataset.command,false,button.dataset.value||null);rememberSelection();sync()}));
    editor.querySelector('[data-action="link"]')?.addEventListener('click',()=>{rememberSelection();const url=window.prompt('Masukkan alamat tautan (https://...)');if(url){restoreSelection();document.execCommand('createLink',false,url);sync()}surface.focus()});
    editor.querySelector('[data-action="image"]')?.addEventListener('click',()=>{rememberSelection();imageInput?.click()});
    editor.querySelectorAll('[data-action="image-align"]').forEach(button=>button.addEventListener('click',()=>{if(!selectedFigure){showStatus('Klik gambar di dalam editor terlebih dahulu.',true);return}selectedFigure.className=`article-image align-${button.dataset.align} is-selected`;if(button.dataset.align==='full')selectedFigure.dataset.width='100';selectedFigure.querySelector('[data-resize-label]').textContent=`${selectedFigure.dataset.width}%`;sync();showStatus('Posisi gambar diperbarui.')}));
    imageInput?.addEventListener('change',async()=>{const file=imageInput.files?.[0];if(!file)return;showStatus('Mengunggah gambar...');const data=new FormData();data.append('image',file);const slug=document.querySelector('#slug')?.value.trim(),title=document.querySelector('#title')?.value.trim();data.append('folder_name',slug||title||'berita-baru');try{const response=await fetch(editor.dataset.uploadUrl,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json'},body:data});const result=await response.json();if(!response.ok)throw new Error(result.message||Object.values(result.errors||{}).flat()[0]||'Gambar gagal diunggah.');restoreSelection();const figure=document.createElement('figure');figure.className='article-image align-center';figure.dataset.width='70';const image=document.createElement('img');image.src=result.url;image.alt=result.alt||file.name;const caption=document.createElement('figcaption');caption.textContent='Klik untuk menulis keterangan gambar';figure.append(image,caption);decorateFigure(figure);const range=window.getSelection()?.rangeCount?window.getSelection().getRangeAt(0):null;if(range){range.deleteContents();range.insertNode(figure);range.setStartAfter(figure);range.collapse(true);const selection=window.getSelection();selection.removeAllRanges();selection.addRange(range);savedRange=range.cloneRange()}else surface.append(figure);selectedFigure=figure;figure.classList.add('is-selected');sync();showStatus('Gambar berhasil disisipkan. Klik gambar lalu tarik sudut kanan bawah untuk mengubah ukurannya.')}catch(error){showStatus(error.message||'Gambar gagal diunggah.',true)}finally{imageInput.value=''}});
    editor.closest('form')?.addEventListener('submit',sync);
  });
  const title=document.querySelector('#title'),titleCount=document.querySelector('#title-count');if(title&&titleCount){const count=()=>titleCount.textContent=title.value.length;title.addEventListener('input',count);count()}
};

window.initAdminPage = initAdminPage;

initAjaxNavigation({
  rootSelector: '.wrapper',
  onRender: initAdminPage,
});

initAdminPage();
