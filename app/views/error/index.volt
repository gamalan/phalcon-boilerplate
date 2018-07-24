
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="content">
            <div class="text-center">
                <h1>{{ code }}</h1>
                <span class="sorry">{{ message }}</span>
            </div>
            {% if debug %}
                <div class="col-md-12 error-debug">
                    <div>
                        Error [{{ error.type() }}]: {{ error.message() }} <br>
                        File: <code>{{ error.file() }}</code><br>
                        Line: <code>{{ error.line() }}</code>
                    </div>
                    {% if error.isException() %}
                        <pre>{{ error.exception().getTraceAsString() }}</pre>
                    {% endif %}
                </div>
            {% endif %}
            <div class="col-md-12 text-center">
                <p>
                    We hope to solve it shortly.
                    Please check back in a few minutes. If you continue seeing this error please contact us at
                    <a href="{{ 'mailto:' ~ config.mail.fromEmail }}">{{ config.mail.fromEmail }}</a>
                </p>
                <p>
                    <a class="btn btn-primary" href="/" style="color: #fff;">Back to main page</a>
                </p>
            </div>
        </div>
    </div>
</div>

