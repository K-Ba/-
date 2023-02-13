<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
    </head>
    <title>
        掲示板
    </title>
    <body>
     <?php
// DB接続設定　dsnには空白を開けない PDOでMySQLサーバに接続する

     $dsn = 'データベース名';
     $user = 'ユーザー名';
     $password = 'パスワード';
     $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
//CREATE文：データベース内にテーブルを作成
    $sql="CREATE TABLE IF NOT EXISTS tb51"
    ."("
    ."id INT AUTO_INCREMENT PRIMARY KEY,"
    ."name char (32),"
    ."comment TEXT,"
    ."ptime datetime,"
    ."password char(32)"
    .");";
    $stmt=$pdo->query($sql);

    if(!empty($_POST["hensyu"])){$hensyu=($_POST["hensyu"]);}
    if(!empty($_POST["henpas"])){$henpas=($_POST["henpas"]);}

           //新規投稿
    if(isset($_POST["submit"])){
        if(!empty($_POST["name"]) && !empty($_POST["komento"]) && !empty($_POST["password"])){
            $name=$_POST["name"];
            $komento=$_POST["komento"];
            $password=$_POST["password"];
            if(empty($_POST["new"])){
            
            //データベース書き込み cast(? as datetime)で文字列型からdatetime型に変換する
                $sql=$pdo->prepare("INSERT INTO tb51 (name,comment,ptime,password) values(:name,:comment,cast(now()as datetime),:password)");
                $sql->bindparam(":name",$name,PDO::PARAM_STR);
                $sql->bindParam(":comment",$komento,PDO::PARAM_STR);
                $sql->bindParam(":password",$password,PDO::PARAM_STR);
                $sql->execute();
            }
                 //編集モード
             else{
                $new=$_POST["new"];
                $sql="UPDATE tb51 SET name=:name, comment=:comment, password=:password WHERE id=:id";
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(":name",$name,PDO::PARAM_STR);
                $stmt->bindParam(":comment",$komento,PDO::PARAM_STR);
                $stmt->bindParam(":password",$password,PDO::PARAM_STR);
                $stmt->bindParam(":id",$new,PDO::PARAM_STR);
                $stmt->execute();
             }
        }
           
           //削除機能の実装
    }
            elseif(isset($_POST["submit2"])){
    //「番号とパスのフォームが埋まっているなら」
                 if(!empty($_POST["sakujo"]) && !empty($_POST["sakupas"])){
        //入力された削除番号の投稿を取り出す
                    $sakujo=$_POST["sakujo"];
                    $sakupas=$_POST["sakupas"];
                    $stmt=$pdo->prepare("SELECT * FROM tb51 where id=:id");
                    $stmt->bindParam(":id",$sakujo,PDO::PARAM_INT);
                    $stmt->execute();
                    $result=$stmt->fetch();
        //「パスが正しいなら」そのパスをフォームに入力されたパスと比較
                    if($result["password"]==$sakupas){
            //一致したらその行を削除
                        $stmt=$pdo->prepare("DELETE FROM tb51 where id=:id");
                        $stmt->bindparam(":id",$sakujo,PDO::PARAM_INT);
                        $stmt->execute();
                        echo $sakujo."番の投稿を削除しました";
                    }else{
                        echo "パスワードが違います";
                    }
                    }else{
                        echo "削除番号とパスワードを入力してください";
                    }
                }

            
           //編集処理
                     elseif(isset($_POST["submit3"])){
                        if(!empty($_POST["hensyu"])&&!empty($_POST["henpas"])){
                            $stmt=$pdo->prepare("SELECT * FROM tb51 where id=:id");
                            $stmt->bindParam(":id",$hensyu,PDO::PARAM_INT);
                            $stmt->execute();
                            $result=$stmt->fetch();
                            if($result["password"]==$henpas){
                                echo "編集モード：".$hensyu."番の投稿";
                            }else{
                                echo "パスワードが違います";
                            }
                            }else{
                                echo "編集番号とパスワードを入力してください";
                            }
                        }
        ?>
        <form action="" method="post">
        【投稿フォーム】
          <br>
        <input type="text" name="name" placeholder="名前入力欄" value="<?php
    //投稿番号とパスが一致した時、その投稿の名前を取り出す
            if(isset($_POST["submit3"]) && !empty($_POST["hensyu"]) && !empty($_POST["henpas"])){
                $stmt=$pdo->prepare("SELECT * FROM tb51 where id=:id");
                $stmt->bindParam(":id",$hensyu,PDO::PARAM_INT);
                $stmt->execute();
                $result=$stmt->fetch();
                if($result["password"]==$henpas){
                    echo $result["name"];
                }
            }
        ?>">
        <br>
        <input type="text" name="komento" placeholder="コメント入力欄" value="<?php
            if(isset($_POST["submit3"]) && !empty($_POST["hensyu"]) && !empty($_POST["henpas"])){
                $stmt=$pdo->prepare("SELECT * FROM tb51 where id=:id");
                $stmt->bindParam(":id",$hensyu,PDO::PARAM_INT);
                $stmt->execute();
                $result=$stmt->fetch();
                if($result["password"]==$henpas){
                    echo $result["comment"];
                }
            }
        ?>">
        <br>
        <input type="password" name="password" placeholder="パスワード入力欄" value="<?php
        //投稿番号とパスが一致した時、その投稿の名前を取り出す
            if(isset($_POST["submit3"]) && !empty($_POST["hensyu"]) && !empty($_POST["henpas"])){
                $stmt=$pdo->prepare("SELECT * FROM tb51 where id=:id");
                $stmt->bindParam(":id",$hensyu,PDO::PARAM_INT);
                $stmt->execute();
                $result=$stmt->fetch();
                if($result["password"]==$henpas){
                    echo $result["password"];
                }
            }
        ?>">
        <br>
        <input type="hidden" name="new" value="<?php if(isset($_POST["submit3"]) && !empty($hensyu)){echo $hensyu;} ?>" >
        <input type="submit" name="submit"> 
        <br>
        【削除フォーム】
        <br>
        <input type="number" name="sakujo" placeholder="削除対象番号入力欄"> <br>
        <input type="password" name="sakupas" placeholder="パスワード入力欄"><br>
        <input type="submit" name="submit2"><br>
        【編集フォーム】<br>
        <input type="number" name="hensyu" placeholder="編集対象番号入力欄"> <br>
        <input type="password" name="henpas" placeholder="パスワード入力欄"><br>
        <input type="submit" name="submit3"> <br>
    </form>
         <?php //データベースの中身をブラウザに表示
    echo "<hr>";
    $stmt=$pdo->query("SELECT * FROM tb51");
    $results=$stmt->fetchall();
    foreach($results as $row){
       echo'<span style="color:#0000ff;">'.$row["id"].'</span>'."  "."名前：".$row["name"]."<>"."投稿日：".$row["ptime"]."<br>"."  ".$row["comment"]."<br>";
    }
    echo "<hr>";
    ?>
    </body>
</html>