<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('admin.title') }}</title>
    <link href="{{ asset('vendor/inspinia-admin/inspinia/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/inspinia-admin/inspinia/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/inspinia-admin/inspinia/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/inspinia-admin/inspinia/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/inspinia-admin/admin/css/admin.css') }}" rel="stylesheet">
</head>
<body class="gray-bg">
    <div class="loginColumns animated fadeInDown">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="font-bold text-center">{{ config('admin.title') }}</h2>
                <div class="ibox-content">
                    <form id="mform">
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" placeholder="用户名">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="密码">
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-7">
                                    <input type="text" class="form-control" name="img_captcha" placeholder="图形验证码">
                                </div>
                                <div class="col-5" style="height: 34px;">
                                    <img src="{{ captcha_src() }}" class="golden_captcha" title="点击刷新" onclick="$(this).prop('src', $(this).prop('src').split('?')[0] + '?' + Math.random())">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary block full-width m-b">登录</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/inspinia-admin/inspinia/js/jquery-2.1.1.js') }}"></script>
    <script src="{{ asset('vendor/inspinia-admin/inspinia/js/plugins/fullcalendar/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/inspinia-admin/admin/js/plugins/layer/layer.js') }}"></script>
    <script src="{{ asset('vendor/inspinia-admin/admin/js/admin.js') }}"></script>
    <script>
        @if(session('layer_msg'))
        layer.alert('{{ session('layer_msg') }}');
        @endif
        @if(isset($errors) && count($errors))
        layer.msg('{{ $errors->first() }}', {shift: 6});
        @endif

        $('#mform').submit(function() {
            var $this = $(this);
            window.form_submit = $this.find('[type=submit]');
            form_submit.prop('disabled', true);
            $.ajax({
                url: '{{ route('admin::login') }}',
                data: $this.serializeArray(),
                success: function (result) {
                    if (result.err) {
                        form_submit.prop('disabled', false);
                        layer.msg(result.msg, {shift: 6});
                        $('.golden_captcha').click();
                        return false;
                    }
                    layer.msg(result.msg, {icon: 1, time: 1000}, function() {
                        if (result.reload) {
                            location.reload();
                        }
                        if (result.redirect) {
                            location.href = result.redirect;
                        }
                    });
                }
            });
            return false;
        });
    </script>
</body>
</html>