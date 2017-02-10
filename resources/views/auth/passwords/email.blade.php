@extends('auth.layouts.app')

<!-- Main Content -->
@section('content')
<div id="LoginPage" class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                {{--<div class="panel-heading">Reset Password</div>--}}
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!session('status'))
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/email') }}">
                        {{ csrf_field() }}

                        <div style="padding-top: 100px;" class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">

                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Enviar
                                </button>
                            </div>
                        </div>
                    </form>
                    @else
                    <a class="goback" href="/auth/login">Ir para Login</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
