<form action="<?php echo APP_URL ?>/item/add" method="post">
    <input type="text" value="click to insert" onclick="this.value=''" name="value">
    <input type="submit" value="insert">
</form>
<br/><br/>

<?php $number = 0?>

<?php foreach ($items as $item): ?>
    <a class="big" href="<?php echo APP_URL ?>/item/view/<?php echo $item['id'] ?>" title="click to edit">
        <span class="item">
            <?php echo ++$number ?>
            <?php echo $item['item_name'] ?>
        </span>
    </a>
    ----
    <a class="big" href="<?php echo APP_URL ?>/item/delete/<?php echo $item['id']?>">delete</a>
    <br/>
<?php endforeach ?>

<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:45 PM
 */