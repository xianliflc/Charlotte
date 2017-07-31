<form action="<?php echo APP_URL ?>/item/update" method="post">
    <input type="text" name="value" value="<?php echo $item['item_name'] ?>">
    <input type="hidden" name="id" value="<?php echo $item['id'] ?>">
    <input type="submit" value="edit">
</form>

<a class="big" href="<?php echo APP_URL ?>/item/index">return</a>
<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:46 PM
 */