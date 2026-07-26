import { initAjaxNavigation } from './ajax';
import {
    bindImagePreparation,
    prepareImageFile,
    prepareImageInput
} from './image-upload';
import { initRegionSelectors } from './regions';

import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

// Tambahan untuk skin TinyMCE pada Vite
import 'tinymce/skins/ui/oxide/skin.js';
import 'tinymce/skins/ui/oxide/content.js';
import 'tinymce/skins/content/default/content.js';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/wordcount';

const initAdminPage = () => {
  const successDialog=document.querySelector('[data-success-dialog]');
  if(successDialog){
    const message=successDialog.dataset.message||'Perubahan berhasil disimpan.';
    successDialog.remove();
    if(window.Swal){
      window.Swal.fire({
        title:'Berhasil!',
        text:message,
        icon:'success',
        confirmButtonText:'Oke',
        confirmButtonColor:'#526b42',
        allowOutsideClick:false,
        returnFocus:false,
      });
    }else{
      window.alert(message);
    }
  }
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
  document.querySelectorAll('[data-submit-status]').forEach(button=>{
    if(button.dataset.submitStatusBound)return;
    button.dataset.submitStatusBound='true';
    button.addEventListener('click',()=>{
      const status=button.form?.querySelector('[data-publication-status]');
      if(status)status.value=button.dataset.submitStatus;
    });
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
    const empty=()=>{
      preview.innerHTML='<i class="fa fa-picture-o" aria-hidden="true"></i><span>Belum ada gambar</span>';
      preview.classList.add('is-empty');
      preview.setAttribute('aria-label','Pilih gambar untuk diunggah');
    };
    const show=url=>{
      if(!url){empty();return}
      preview.innerHTML='';
      preview.classList.remove('is-empty');
      preview.setAttribute('aria-label','Pratinjau gambar');
      const image=document.createElement('img');
      image.src=url;
      image.alt=alt.value;
      preview.appendChild(image);
      const clear=document.createElement('button');
      clear.type='button';
      clear.className='image-picker-remove';
      clear.dataset.imageClear='';
      clear.dataset.toggle='tooltip';
      clear.title='Hapus gambar';
      clear.setAttribute('aria-label','Hapus gambar');
      clear.innerHTML='<i class="fa fa-trash" aria-hidden="true"></i>';
      preview.appendChild(clear);
      clear.addEventListener('click',clearImage);
      if(window.jQuery)window.jQuery(clear).tooltip();
    };
    const clearImage=()=>{
      select.value='';
      upload.value='';
      alt.value='';
      remove.value='1';
      empty();
    };
    const openUpload=event=>{
      if(event.target.closest?.('[data-image-clear]'))return;
      if(!preview.classList.contains('is-empty')||!upload)return;
      if(event.type==='keydown'&&!['Enter',' '].includes(event.key))return;
      event.preventDefault();
      upload.click();
    };
    preview?.addEventListener('click',openUpload);
    preview?.addEventListener('keydown',openUpload);
     upload?.addEventListener('change',async()=>{
       const file=upload.files?.[0];
       if(!file)return;
       select.value='';
       remove.value='0';
       show(URL.createObjectURL(file));
       if(!alt.value)alt.value=file.name.replace(/\.[^/.]+$/,'');
       await prepareImageInput(upload);
     });
    alt?.addEventListener('input',()=>{
      const image=preview.querySelector('img');
      if(image)image.alt=alt.value;
    });
    picker.querySelector('[data-image-clear]')?.addEventListener('click',clearImage);
     bindImagePreparation(upload?.form);
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
     camera?.addEventListener('change',async()=>{
       const file=camera.files?.[0];
       if(!file||!imagePreview)return;
       imagePreview.innerHTML='';
      const image=document.createElement('img');
      image.src=URL.createObjectURL(file);
      image.alt='Pratinjau foto dari kamera';
       imagePreview.appendChild(image);
       const remove=officialForm.querySelector('[data-image-remove]');
       if(remove)remove.value='0';
       const prepared=await prepareImageInput(camera);
       if(prepared&&prepared!==file){
         image.src=URL.createObjectURL(prepared);
       }
     });
     bindImagePreparation(officialForm);
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
  const initTinyMce = async () => {
    const fields = Array.from(document.querySelectorAll('textarea[data-tinymce]'));
    if (!fields.length) return;
    tinymce.remove();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const uploadUrl = document.querySelector('[data-tinymce-upload-url]')?.dataset.tinymceUploadUrl;
    await tinymce.init({
      selector: 'textarea[data-tinymce]',
      license_key: 'gpl',
      menubar: false,
      height: 520,
      placeholder: fields[0].dataset.placeholder || 'Mulai tulis konten di sini...',
      branding: false,
      promotion: false,
      statusbar: false,
      plugins: 'advlist autolink anchor charmap code fullscreen image link lists media preview searchreplace table visualblocks wordcount',
      toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist blockquote | link image media table | removeformat code fullscreen preview',
      toolbar_mode: 'sliding',
      image_caption: true,
      image_advtab: true,
      image_class_list: [
        { title: 'Gambar artikel', value: 'article-image align-center' },
        { title: 'Rata kiri', value: 'article-image align-left' },
        { title: 'Rata kanan', value: 'article-image align-right' },
        { title: 'Lebar penuh', value: 'article-image align-full' },
      ],
      images_file_types: 'jpg,jpeg,png,webp',
      automatic_uploads: true,
      relative_urls: false,
      remove_script_host: false,
      content_style: `
        body { font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.65; }
        body.mce-content-body[data-mce-placeholder]::before { font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, sans-serif; }
        figure.image, figure.article-image { width: 70%; margin: 18px auto; }
        figure.image.align-left, figure.article-image.align-left { float: left; width: 45%; margin: 8px 18px 12px 0; }
        figure.image.align-right, figure.article-image.align-right { float: right; width: 45%; margin: 8px 0 12px 18px; }
        figure.image.align-full, figure.article-image.align-full { width: 100%; }
        figure figcaption { padding: 6px; color: #6f786f; font-size: 12px; text-align: center; cursor: text; }
        figure figcaption:empty::before { content: "Klik untuk menulis keterangan gambar"; color: #9ba39c; }
      `,
      images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
        if (!uploadUrl) {
          reject('URL upload gambar tidak tersedia.');
          return;
        }
        const data = new FormData();
        data.append('image', blobInfo.blob(), blobInfo.filename());
        const slug = document.querySelector('#slug')?.value.trim();
        const title = document.querySelector('#title')?.value.trim();
        data.append('folder_name', slug || title || 'berita-baru');
        fetch(uploadUrl, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
          body: data,
        })
          .then(response => response.json().then(result => ({ response, result })))
          .then(({ response, result }) => {
            if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Gambar gagal diunggah.');
            resolve(result.location || result.url);
          })
          .catch(error => reject(error.message || 'Gambar gagal diunggah.'));
      }),
      setup: editor => {
        editor.on('init change input undo redo keyup', () => editor.save());
        editor.on('BeforeSetContent', event => {
          event.content = event.content.replaceAll('<figure class="image">', '<figure class="article-image align-center">');
        });
        editor.on('ObjectSelected', event => {
          if (event.target?.nodeName === 'IMG') {
            const figure = event.target.closest('figure');
            if (figure && !figure.classList.contains('article-image')) figure.classList.add('article-image', 'align-center');
          }
        });
      },
    });
  };
  initTinyMce();
  const title=document.querySelector('#title'),titleCount=document.querySelector('#title-count');if(title&&titleCount){const count=()=>titleCount.textContent=title.value.length;title.addEventListener('input',count);count()}
  initRegionSelectors();
  document.querySelectorAll('form').forEach(bindImagePreparation);
};

window.initAdminPage = initAdminPage;

initAjaxNavigation({
  rootSelector: '.wrapper',
  onRender: initAdminPage,
});

initAdminPage();
