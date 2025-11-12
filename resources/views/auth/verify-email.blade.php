@extends('layouts.app')

@section('title', 'E-posta Doğrulama')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="bi bi-envelope-check"></i> E-posta Doğrulama</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Devam etmeden önce e-posta adresinizi doğrulamanız gerekmektedir. 
                        Size e-posta gönderildi, lütfen gelen kutunuzu kontrol edin.
                    </div>

                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> 
                            Yeni bir doğrulama bağlantısı e-posta adresinize gönderildi.
                        </div>
                    @endif

                    <p>E-posta almadıysanız, tekrar göndermek için aşağıdaki butona tıklayabilirsiniz.</p>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope"></i> Doğrulama E-postasını Tekrar Gönder
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
