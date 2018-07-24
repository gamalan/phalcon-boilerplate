<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="content">
            <div class="text-center">
                <h1>{{ code }}</h1>
                <span class="sorry">{{ message }}</span>
            </div>
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
{% if sentry_dsn is defined %}
    <script src="https://cdn.ravenjs.com/3.23.1/raven.min.js"></script>
    <script>
        var appDSN = "{{ sentry_dsn }}";
        //Raven.config(appDSN).install();
        {% if errorid is defined and errorid != null %}
        Raven.showReportDialog({
            eventId: "{{ errorid }}",
            dsn: appDSN
        });
        {% endif %}
    </script>
{% endif %}