Plugin Renovar claves de acceso<br/><br/>
En la configuración del plugin el administrador puede indicar el número de días que las claves son válidas.<br/>
A través del cron (recomendable una ejecución al día) comprobará la fecha en que se actualizaron las claves y si no es válida, resetea y envía las nuevas claves de acceso al usuario.<br/>
El plugin permite la ejecución manual del fichero que actualiza las claves. <br/>
El fichero que debe ejecutar el cron es /plugin/renewpassword/src/cron_renew_password.php
