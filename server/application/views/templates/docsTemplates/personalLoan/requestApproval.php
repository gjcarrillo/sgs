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
<body class="body-text" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif;font-size: 14px;">
<img src="<?php echo base_url(); ?>images/logo_ipapedi_large.png" height="100">
<br /><br /><br/>
<p>
    Mediante la presente se le notifica al beneficiario <?php echo $username; ?> CI: <?php echo $userId; ?> que, en base a su siguiente solicitud:
<ul>
    <li>Identificador: <?php echo $requestId; ?></li>
    <li>Tipo de préstamo:  <?php echo $loanTypeString; ?></li>
    <li>Monto solicitado: Bs.  <?php echo number_format($reqAmount, 2); ?></li>
    <li>Teléfono de contacto:  <?php echo $tel; ?></li>
    <li>Correo electrónico:  <?php echo $email; ?></li>
    <li>Cuotas a pagar: Bs. <?php echo number_format($paymentFee, 2); ?>, durante un periodo de  <?php echo $due; ?> <?php echo $due == 1 ? 'mes.' : 'meses consecutivos.'; ?></li>
</ul>
<br/>
<p>Se le ha aprobado un monto de Bs. <?php echo number_format($approvedAmount, 2); ?>, del cual se le realizó el siguiente abono:</p>
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
                        Monto aprobado
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($approvedAmount, 2); ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($approvedAmount, 2); ?>
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        Cuota del préstamo anterior
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($lastLoanFee, 2); ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[0], 2); ?>
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        Interés del préstamo nuevo en <?php echo $newLoanInterestDays; ?> <?php echo $newLoanInterestDays == 1 ? 'día' : 'días '; ?>
                    </td>
                    <td class="alignright" style="color: red; font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($newLoanInterestFee, 2) . '-'; ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[1], 2); ?>
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        Abono (20%) para deudas de gastos médicos
                    </td>
                    <td class="alignright" style="color:red; font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($medicalContribution, 2) . '-'; ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[2], 2); ?>
                    </td>
                </tr>
                <tr style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                        Saldo del préstamo anterior
                    </td>
                    <td class="alignright" style="color:red; font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($lastLoanBalance, 2) . '-'; ?>
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans','Helvetica Neue',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                        <?php echo number_format($totals[3], 2); ?>
                    </td>
                </tr>
                <?php
                if ($deductionsTotal > 0) {
                    echo '<tr style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">';
                    echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">';
                    echo 'Abono para deudas de otros préstamos';
                    echo '</td>';
                    echo '<td class="alignright" style="color:red; font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">';
                    echo number_format($deductionsTotal, 2) . '-';
                    echo '</td>';
                    echo '<td class="alignright" style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">';
                    echo number_format($totals[4], 2);
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
                <tr class="total" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;"valign="top">Total</td>
                    <td class="alignright" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;" align="right" valign="top">
                    </td>
                    <td class="alignright" style="font-family: 'Open Sans, Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 700; margin: 0; padding: 5px 0;" align="right" valign="top">
                        Bs. <?php echo number_format($totals[4], 2); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php
if ($deductionsTotal > 0) {
    echo '<br/>Se entiende además que fue bajo la solicitud del beneficiario que se realizó ' .
         'la deducción adicional de Bs. ' . number_format($deductionsTotal, 2) . ' para pagar deudas '.
         'de otros préstamos, como se detalla a continuación: <br/>';
    echo '<table class="invoice" style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 20px auto;">';
    echo '<tr style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">';
    echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 5px 0;" valign="top">';
    echo '<table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0;">';
    echo '<tr style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">';
    echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top">';
    echo 'Préstamo';
    echo '</td>';
    echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top" align="right">';
    echo 'Deducción (Bs)';
    echo '</td>';
    echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 2px; border-top-color: #333; border-top-style: solid; border-bottom-color: #333; border-bottom-width: 2px; border-bottom-style: solid; margin: 0; padding: 5px;" valign="top" align="right">';
    echo 'Total (Bs)';
    echo '</td>';
    echo '</tr>';
    $acum = 0;
    foreach ($deductions as $deduction) {
        echo '<tr style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">';
        echo '<td style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">';
        echo $deduction['description'];
        echo '</td>';
        echo '<td class="alignright" style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">';
        echo number_format($deduction['amount'], 2);
        echo '</td>';
        echo '<td class="alignright" style="font-family: \'Open Sans\',\'Helvetica Neue\',Helvetica,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">';
        $acum += $deduction['amount'];
        echo number_format($acum, 2);
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
?>

Constancia expedida a los días <?php echo $date->format('d') ?> del mes <?php echo $date->format('m') ?>, del año
<?php echo $date->format('Y') ?>.<br />
</p>
</body>
</html>
