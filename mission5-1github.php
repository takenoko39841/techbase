<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>mission5-1</title>
    </head>
    <body>
        <?php
            // 編集する際のフォームのエラー文を表示しないため(存在しない編集番号を指定すると起こる)
            $name = null;
            $comment = null;
            $password_e = null;
            $edit_c = null;

            // DB接続設定
            $dsn = 'mysql:dbname=********;host=localhost';  // ''内にスペースを入れないこと
            $user = '*******';
            $password = '*******';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            // テーブルを作成
            $sql = "CREATE TABLE IF NOT EXISTS board"
            . " ("
            . "id INT AUTO_INCREMENT PRIMARY KEY,"
            . "name char(32),"
            . "comment TEXT,"
            . "now TEXT,"
            . "password TEXT"
            . ");";
            $stmt = $pdo->query($sql);

            // コメントフォームの処理
            if(isset($_POST['comment'])) {
                if($_POST['comment'] != null) {
                    if(!empty($_POST['password'])) {
                        if(!empty($_POST['edit_num'])) {
                        // 編集する
                            $id = $_POST['edit_num'];
                            $name = $_POST['name'];
                            $comment = $_POST['comment'];
                            $password = $_POST['password'];
                            // パスワード判定するためにパスワードを取得
                            $sql = 'SELECT * FROM board';
                            $stmt = $pdo->query($sql);
                            $results = $stmt->fetchAll();
                            foreach($results as $row) {
                                if($row['id'] == $id) {
                                    $password_t = $row['password'];
                                    break;
                                }
                            }
                            if($password == $password_t) {
                                if(empty($name)) {
                                    $name = "名無しさん";
                                }
                                $sql = 'UPDATE board SET name=:name,comment=:comment WHERE id=:id';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                                $stmt->execute();
                            } else {
                                echo "Error: パスワードが違います ※自動入力されているのは正しいパスワードです";
                            }
                        } else {
                        // 新規書き込み
                            $comment = $_POST['comment'];
                            $name = $_POST['name'];
                            if(empty($name)) {
                                $name = "名無しさん";
                            }
                            $now = date("Y/m/d H:i:s");
                            $password = $_POST['password'];
                            $sql = $pdo -> prepare("INSERT INTO board (name, comment, now, password) VALUES (:name, :comment, :now, :password)");
                            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                            $sql -> bindParam(':now', $now, PDO::PARAM_STR);
                            $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                            $sql -> execute();
                        }
                    } else {
                        echo "Error: パスワードを入力してください";
                    }
                } else {
                    echo "Error: コメントを入力してください";
                }
            }
            
            // 削除フォーム
            if(isset($_POST['delete'])) {
                if($_POST['delete'] != null) {
                    if(isset($_POST['pass_del'])) {
                        $password = $_POST['pass_del'];
                        $id = $_POST['delete'];
                        $password_d = null;
                        // 投稿のパスワードを取得
                        $sql = 'SELECT * FROM board';
                        $stmt = $pdo->query($sql);
                        $results = $stmt->fetchAll();
                        foreach($results as $row) {
                            if($row['id'] == $id) {
                                $password_d = $row['password'];
                                break;
                            }
                        }
                        if($password_d == $password) {
                            $sql = 'SELECT count(id) FROM board';
                            $stmt = $pdo->query($sql);

                            $sql = 'delete from board where id=:id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();
                        } else if($password == null) {
                            echo "Error: パスワードを入力してください";
                        } else {
                            echo "Error: パスワードが違います";
                        }
                        if($password_d == null) {
                            echo "Error: 指定した番号の投稿が存在しません";
                        }
                    }
                } else {
                    echo "Error: 削除番号を指定してください";
                }
            }

            // 編集番号フォーム
            if(isset($_POST['edit'])){
                if($_POST['edit'] != null){
                    if(isset($_POST['pass_edit'])){
                        $test = null;
                        $edit = $_POST['edit'];
                        $pass_edit = $_POST['pass_edit'];
                        $sql = 'SELECT * FROM board';
                        $stmt = $pdo->query($sql);
                        $results = $stmt -> fetchAll();
                        foreach($results as $row){
                            if($row['id'] == $edit){
                                if($row['password'] == $pass_edit){
                                    $test = 1;
                                    $edit_c = $row['id'];
                                    $name = $row['name'];
                                    $comment = $row['comment'];
                                    $password_e = $row['password'];
                                    break;
                                } else if($pass_edit == null) {
                                    $test = 1;
                                    echo "Error: パスワードを入力してください";
                                    break;
                                } else {
                                    $test = 1;
                                    echo "Error: パスワードが違います";
                                    break;
                                }
                            }
                        }
                        if($test == null) {
                            echo "Error: 指定した番号の投稿が存在しません";
                        }
                    }
                } else {
                    echo "Error: 編集番号を指定してください";
                }
            }
        ?>
        <!--新規書き込み兼編集用フォーム-->
        <p>【　投稿フォーム　】</p>
        <form action="" method="post">
            <input type="text" name="name" placeholder="名前" value="<?php if(!empty($_POST['edit'])) { echo $name; } ?>">
            <input type="hidden" name="edit_num" value="<?php if(!empty($_POST['edit'])) { echo $edit_c; } ?>">
            <br>
            <input type="text" name="comment" placeholder="コメント" value="<?php if(!empty($_POST['edit'])) { echo $comment; } ?>">
            <br>
            <input type="password" name="password" placeholder="パスワード" value="<?php if(!empty($_POST['edit'])) { echo $password_e; } ?>">
            <br>
            <input type="submit" name="submit1">
        </form>
        <br>
        <p>【　削除フォーム　】</p>
        <form action="" method="post">
            <input type="number" name="delete" placeholder="削除対象番号">
            <br>
            <input type="password" name="pass_del" placeholder="パスワード">
            <br>
            <input type="submit" value="削除">
        </form>
        <br>
        <p>【　編集フォーム　】</p>
        <form action="" method="post">
            <input type="number" name="edit" placeholder="編集対象番号">
            <br>
            <input type="password" name="pass_edit" placeholder="パスワード">
            <br>
            <input type="submit" value="編集">
        </form>

        <hr>
        <p>【投稿一覧】</p>
        <?php
            // テーブルの中身を表示
            $sql = 'SELECT * FROM board';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach($results as $row) {
                echo $row['id'] . ". ";
                echo $row['name'];
                echo "「" . $row['comment'] . "」";
                echo $row['now'] . "<br>";
            }
        ?>
    </body>
</html>