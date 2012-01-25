<?php

class ErrorView
{
	public static function showError($number = 0, $message = "Unknown error", $image = null)
	{
		global $ROOT_PATH;

		$hmessage = htmlspecialchars($message);

		$number = intval($number);

		echo <<<END
<div class="alert-message error">
<h4>Erreur $number</h4>
<p>$hmessage</p>
</div>
<div class="alert-message block-message error">
<p>
	<img src="$ROOT_PATH/Img/photos/$image" alt="" />
</p>
</div>
END;
	}
}
?>
