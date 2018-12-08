<?php

$mail = "in4in.q@gmail.com";
$theme = "Заказ елки";

$data = implode(", ", $_POST);

die(mail($mail, $theme, $data));