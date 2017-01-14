<html>
<head>
    <style>
        .validation-wrapper {
            text-align: center;
        }
        .validation-link {
            display: inline-block;
        }
    </style>
</head>
<body>
<br /><br />
<p>
    Estimado usuario.<br /><br/>
    Hemos recibido la solicitud de un nuevo préstamo con la siguiente información:<br /><br />
</p>
<ul>
    <li>Identificador: <?php echo str_pad($reqId, 6, '0', STR_PAD_LEFT); ?></li>
    <li>Solicitante: <?php echo $username; ?>, CI:  <?php echo $userId; ?></li>
    <li>Tipo de préstamo: <?php echo $loanTypeString; ?></li>
    <li>Fecha de creación: <?php echo $creationDate; ?></li>
    <li>Monto solicitado: Bs.  <?php echo number_format($reqAmount, 2); ?></li>
    <li>Teléfono de contacto:  <?php echo $tel; ?></li>
    <li>Correo electrónico:  <?php echo $email; ?></li>
    <li>Cuotas a pagar: Bs. _________, durante un periodo de  <?php echo $due; ?> meses. *</li>
</ul>
<br />
<p>
    Una vez verificada la información por favor haga clic en "Validar solicitud", con la finalidad de validar
    su solicitud. Es importante resaltar que una vez validada, ésta <b>no podrá ser eliminada</b>.
    <br/>
    <div class="validation-wrapper">
        <div class="validation-link">
            <a href="<?php echo $validationURL; ?>">Validar solicitud</a>
        </div>
    </div>
    <br/><br/>
    Si existe algún error con la información provista, por favor realice la correspondiente edición a través
    del sistema y volveremos a enviar el enlace de validación con la información actualizada.<br/><br>

    En caso de haber realizado la solicitud por error, puede eliminarla haciendo clic
    <a href="<?php echo $deleteURL; ?>">aquí</a>.
</p>

</body>
</html>
