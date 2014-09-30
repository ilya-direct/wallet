<table border="1">
    <?php foreach($TPL->table as $row): ?>
    <tr>
        <td>
            <?=$row['sign'] ?>
        </td>
        <td>
            <img src="/images/<?=$row['card']?>_icon.jpg" >
        </td>
        <td>
            <?=$row['sum'] ?>
        </td>
        <td>
            <?=$row['item'] ?>
        </td>
    </tr>
    <? endforeach; ?>
</table>