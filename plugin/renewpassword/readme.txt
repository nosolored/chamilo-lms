Plugin Renovar claves de acceso<br/><br/>
En la configuraci�n del plugin el administrador puede indicar el n�mero de d�as que las claves son v�lidas.<br/>
A trav�s del cron (recomendable una ejecuci�n al d�a) comprobar� la fecha en que se actualizaron las claves y si no es v�lida, resetea y env�a las nuevas claves de acceso al usuario.<br/>
El plugin permite la ejecuci�n manual del fichero que actualiza las claves. <br/>
El fichero que debe ejecutar el cron es /plugin/renewpassword/src/cron_renew_password.php
