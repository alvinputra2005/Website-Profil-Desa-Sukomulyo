@if(session('success'))
    <div data-success-dialog data-message="{{ session('success') }}" hidden></div>
    <noscript>
        <div class="alert alert-success">
            <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
            {{ session('success') }}
        </div>
    </noscript>
@endif
@if(session('status'))<div id="notifikasi" class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert">×</button><h4><i class="icon fa fa-info"></i> Informasi</h4>{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><h4><i class="icon fa fa-ban"></i> Data belum dapat diproses</h4><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
