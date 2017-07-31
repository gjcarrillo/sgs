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
<body style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif;font-size: 14px;">
<img src="<?php echo base_url(); ?>images/logo_ipapedi_large.png" height="100">
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
    <?php echo $requestId; ?> con la siguiente información:<br /><br />

</p>
<ul>
    <li>Solicitante: <?php echo $username; ?>, CI:  <?php echo $userId; ?></li>
    <li>Monto solicitado: Bs.  <?php echo number_format($reqAmount, 2); ?></li>
    <li>Teléfono de contacto:  <?php echo $tel; ?></li>
    <li>Correo electrónico:  <?php echo $email; ?></li>
    <li>Cuotas a pagar: Bs. <?php echo number_format($paymentFee, 2); ?>, durante un periodo de  <?php echo $due; ?> <?php echo $due == 1 ? 'mes.' : 'meses consecutivos.'; ?></li>
</ul>
<br />
<p>
    Dicha información será corroborada tras realizar la correspondiente
    verificación a través del sistema.<br /><br />

    Asimismo, entiendo que el monto <b>máximo</b> a abonar se basa en los siguientes cálculos correspondientes a la fecha actual:
<table class="invoice" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 20px auto;">
    <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 5px 0;" valign="top">
            <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0;">
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top">
                        Descripción
                    </td>
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top" align="right">
                        Monto (Bs)
                    </td>
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top" align="right">
                        Resultado (Bs)
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        Monto del préstamo
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($reqAmount, 2); ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($reqAmount, 2); ?>
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        <?php echo $interest; ?>% de intereses
                    </td>
                    <td class="alignright" style="color:red;font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($reqAmount * $interest / 100, 2) . '-'; ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[0], 2); ?>
                    </td>
                </tr>
                <tr class="total" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;" valign="top">Total</td>
                    <td class="alignright" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;" align="right" valign="top">
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[0], 2); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table><br/>

Constancia expedida a los días <?php echo $date->format('d') ?> del mes <?php echo $date->format('m') ?>, del año
<?php echo $date->format('Y') ?>.<br />
</p>
</body>
</html>
