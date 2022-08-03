<?php
    date_default_timezone_set('Asia/Tashkent');
    
    define('API_KEY', "5538538023:AAGPfkqeVT1_WTL6to-4Kwb6-Rt8K2R2Tm4");
    function bot($method, $datas=[]){
        $url = "https://api.telegram.org/bot".API_KEY."/".$method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);

        $res = curl_exec($ch);

        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }else{
            return json_decode($res);
        }
    }
    function html($tx){
        return str_replace(['<','>'],['<','>'],$tx);
    }
    include 'db.php';
    $update = json_decode(file_get_contents('php://input'));
    $message = $update->message;
    $chat_id = $message->chat->id;
    $type = $message->chat->type;
    $miid =$message->message_id;
    $name = $message->from->first_name;
    $lname = $message->from->last_name;
    $full_name = $name . " " . $lname;
    $full_name = rStr(html($full_name));
    $user = $message->from->username;
    $fromid = $message->from->id;
    $text = rStr(html($message->text));
    $title = $message->chat->title;
    $chatuser = $message->chat->username;
    $chatuser = $chatuser ? $chatuser : "Shaxsiy Guruh!";
    $caption = rStr($message->caption);
    $entities = $message->entities;
    $entities = $entities[0];
    $text_link = $entities->type;
    $left_chat_member = $message->left_chat_member;
    $new_chat_member = $message->new_chat_member;
    $photo = $message->photo;
    $video = $message->video;
    $audio = $message->audio;
    $reply = $message->reply_markup;
    $fchat_id = $message->forward_from_chat->id;
    $fid = $message->forward_from_message_id;
    //editmessage
    $callback = $update->callback_query;
    $qid = $callback->id;
    $mes = $callback->message;
    $mid = $mes->message_id;
    $cmtx = $mes->text;
    $cid = $callback->message->chat->id;
    $ctype = $callback->message->chat->type;
    $cbid = $callback->from->id;
    $cbuser = $callback->from->username;
    $data = $callback->data;
    if (!file_exists("step")) mkdir("step");
    $check = file_get_contents("step/check.txt");
    if ($message) {
        if ($text == "/start") {
            bot('sendMessage',[
                'chat_id'=>$fromid,
                'text'=>"Salom 👋".$full_name.", Botimizga xush kelibsiz, siz bu bot orqali musiqalarni osongina topishingiz mumkin."
            ]);
        }
        if ($fromid == $admin) {
            if ($text == "/on") {
                file_put_contents("step/check.txt", "on");
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>"Barcha musiqa joylay oladi"
                ]);
            }
            if ($text == "/off") {
                file_put_contents("step/check.txt", "on");
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>"Faqat admin musiqa joylay oladi"
                ]);
            }
        }

        $commands = ['/start','help'];
        if (!in_array($text, $commands)) {
            $query = mysqli_query($conn,"SELECT * FROM music_search WHERE artists LIKE '%{$text}%' or title LIKE '%{$text}%' or music LIKE '%$text%'");
            if (mysqli_num_rows($query)>0) {
                $matn = "Natijalar:\n\n";
                $i = 0;
                foreach ($query as $key => $value) {
                    $i++;
                    $matn .= $i . ".  " . $value["artist"] . " - " . $value["title"] . "\n";
                    $keyy[] = ['text'=>$i, 'callback_data'=> 'down_' . $value["id"]];
                    if ($i == 10) {
                        break;
                    }
                }
                $keys = array_chunk($keyy, 5);
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>$matn,
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>$keys
                    ]),
                ]);
            }else{
                $exp = explode(" ", $text);
                $arr = [];
                foreach($exp as $key => $value) {
                    $arr[] = $value;
                }
                $imp = implode("_", $arr);
                $api = file_get_contents("https://u1775.xvest4.ru/API/uzhits.uz/index.php?music=$imp");
                $jd = json_decode($api);
    
                $data = $jd->data;
    
                foreach ($data as $key => $value) {
                    $artist = str_replace("'", "", $value->artist);
                    $title = str_replace("'", "", $value->title);
                    $track = $artist . " " . $title;
                    if(($title != "" || $artist != "") && $value->download_url != ""){
                        $ins = "INSERT INTO music_search (title,artist,music,download_url) VALUES ('{$title}','{$artist}','{$track}','{$value->download_url}')" or die(mysqli_error($conn));
                        $query = mysqli_query($conn, $ins);
                    }
                }
    
                $sltQuery = mysqli_query($conn,"SELECT * FROM music_search WHERE artists LIKE '%{$text}%' or title LIKE '%{$text}%' or music LIKE '%$text%'");
                if (mysqli_num_rows($sltQuery)>0) {
                    $matn = "Natijalar:\n\n";
                    $i = 0;
                    foreach ($sltQuery as $key => $value) {
                        $i++;
                        $matn .= $i . ".  " . $value["artist"] . " - " . $value["title"] . "\n";
                        $keyy[] = ['text'=>$i, 'callback_data'=> 'down_' . $value["id"]];
                        if ($i == 10) {
                            break;
                        }
                    }
                    $keys = array_chunk($keyy, 5);
                    bot('sendMessage',[
                        'chat_id'=>$fromid,
                        'text'=>$matn,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>$keys
                        ]),
                    ]);
                }else{
                    bot('sendMessage', [
                        'chat_id' => $fromid,
                        'text' => "Afsus musiqa topilmadi!"
                    ]);
                }
            }
        }





        if ($text == "/top") {
            $query = mysqli_query($conn,"SELECT * FROM music_search WHERE id > '0' ORDER BY down DESC LIMIT 10");
            if (mysqli_num_rows($query) > 0) {
                $matn = "Eng kop yuklab olingan musiqalar:nn";
                $i = 0;
                foreach ($query as $key => $value) {
                    $i++;
                    $matn .= $i . ".  " . $value["name"] ;
                    $keyy[] = ['text'=>$i, 'callback_data'=> 'down_' . $value["id"]];
                    if ($i == 10) {
                        break;
                    }
                }
                $keys = array_chunk($keyy, 3);
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>$matn,
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>$keys
                    ]),
                ]);
            }
        }
        if ($text == "/random") {
          $query = mysqli_query($conn, "SELECT * FROM music_search WHERE id ORDER BY RAND() LIMIT 10");
          if (mysqli_num_rows($query) > 0) {
                $matn = "Tasodiyif yuborilgan musiqalar:nn";
                $i = 0;
                foreach ($query as $key => $value) {
                    $i++;
                    $matn .= $i . ".  " . $value["name"];
                    $keyy[] = ['text'=>$i, 'callback_data'=> 'down_' . $value["id"]];
                    if ($i == 10) {
                        break;
                    }
                }
                $keys = array_chunk($keyy, 3);
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>$matn,
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>$keys
                    ]),
                ]);
            }
        }
        if ($text == "/last") {
          $query = mysqli_query($conn, "SELECT * FROM music_search WHERE id > '0' ORDER BY id DESC LIMIT 10");
          if (mysqli_num_rows($query) > 0) {
                $matn = "Oxirgi yuklangan 10ta musiqa:nn";
                $i = 0;
                foreach ($query as $key => $value) {
                    $i++;
                    $matn .= $i . ".  " . $value["name"];
                    $keyy[] = ['text'=>$i, 'callback_data'=> 'down_' . $value["id"]];
                    if ($i == 10) {
                        break;
                    }
                }
                $keys = array_chunk($keyy, 3);
                bot('sendMessage',[
                    'chat_id'=>$fromid,
                    'text'=>$matn,
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>$keys
                    ]),
                ]);
            }
        }
    }
    if ($callback) {
        if (mb_stripos($data, 'down_')!==false) {
            $exp = explode("down_", $data);
            $id = $exp[1];
            $query = mysqli_query($conn,"SELECT * FROM music_search WHERE id = '{$id}'");
            if (mysqli_num_rows($query)>0) {
                $row = mysqli_fetch_assoc($query);
                bot('sendAudio',[
                    'chat_id'=>$cbid,
                    'audio'=>$row["download_url"],  
                    'caption'=>$row["caption"]
                ]);
                mysqli_query($conn,"UPDATE music_search SET down = down + '1' WHERE id = '{$id}'");
            }else{
                bot('answerCallbackQuery',[
                    'callback_query_id'=>$qid,
                    'text'=>"Avval musiqa qidiring!",
                    'show_alert'=>true
                ]);
            }
        }
    }
?>