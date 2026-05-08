@extends('layouts.index')

@section('content')
    <img class="card-img-top img-responsive" src="https://tse1.mm.bing.net/th/id/OIP.YBpXSFpgZNTEKo-yQ8_DNQHaEK?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Card image cap">
    <form class="form-horizontal form-material" method="POST" action="{{ route('login') }}">
        @csrf
        <br>
        <div class="form-group ">
            <div class="col-xs-12">
                <input id="name" type="text" class="form-control  @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="off" autofocus placeholder="Gmail de Usuario" />

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Contraseña">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group text-center mt-3">
            <div class="col-xs-12">
                <button type="submit" class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light">
                    {{ __('INGRESAR') }}
                </button>
            </div>
        </div>
    </form>
@endsection
