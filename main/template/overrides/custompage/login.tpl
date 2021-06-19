<div class="custompage">
    <div class="limiter">
        <div class="container-login">
            <div class="wrap-login width-login">
                <form class="login100-form validate-form" action="{{ _p.web }}" method="post">
                    <div class="logo">
                        <img width="250px" class="img-responsive" style="margin:0 auto;" title="{{ _s.site_name }}" src="{{ _p.web_css_theme }}images/header-logo.png">
                    </div>
                    {{ mgs_flash }}
                    {% if error %}
                    <div class="alert alert-warning" role="alert">
                        {{ error }}
                    </div>
                    {% endif %}
                    <div class="form-group">
                        <input type="text" class="form-control username" id="user" name="login" placeholder="{{ 'Username'|get_lang() }}" >
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control password" name="password" id="password" placeholder="{{ 'Password'|get_lang() }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        {{ 'LoginEnter'|get_lang() }}
                    </button>
                    {% if url_register %}
                    <a href="{{ url_register }}" class="btn btn-success btn-block" >
                        {{ 'Registration'|get_lang() }}
                    </a >
                    {% endif %}
                    <div class="last-password">
                        <a href="{{ url_lost_password }}">
                            {{ 'LostPassword'|get_lang() }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

