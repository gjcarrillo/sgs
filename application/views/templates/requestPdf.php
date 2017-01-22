<html>
<head>
    <style>
        body {
            margin: 120px;
            height: 800px;
            width:650px;
        }
        p {
            color: black;
            text-align: justify;
            font-family: sans-serif;
        }

        .body-text {
            line-height: 150%;
        }
    </style>
</head>
<body>
<img src="images/1x1.jpg"/ height="150">
<br /><br />
<p>
    Señores<br />
    <b>Instituto de Previsión Social del Personal Docente y de Investigación
        de la Universidad de Carabobo (IPAPEDI)</b><br />
    Presente.<br /><br />
</p>
<p class="body-text">

    Yo, <?php echo $username; ?>, portador de la cédula de identidad  <?php echo $userId; ?>, hago constar que he realizado la
    solicitud de un préstamo del tipo  <?php echo $loanTypeString; ?>, correspondiente al identificador
    <?php echo $requestId; ?> con las siguientes especificaciones:<br /><br />

</p>
<ul>
    <li>Solicitante: <?php echo $username; ?>, CI:  <?php echo $userId; ?></li>
    <li>Monto solicitado: Bs.  <?php echo number_format($reqAmount, 2); ?></li>
    <li>Teléfono de contacto:  <?php echo $tel; ?></li>
    <li>Correo electrónico:  <?php echo $email; ?></li>
    <li>Cuotas a pagar: Bs. <?php echo number_format($paymentFee, 2); ?>, durante un periodo de  <?php echo $due; ?> meses. *</li>
</ul>
<br />
<p>
    Asimismo, dicha información será corroborada tras realizar la correspondiente
    verificación por correo electrónico.<br /><br /><br />

    Constancia expedida a los días <?php echo $date->format('d') ?> del mes <?php echo $date->format('m') ?>, del año
    <?php echo $date->format('Y') ?>.<br />
</p>
</body>
</html>
