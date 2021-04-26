<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Sawarabi+Mincho" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <title>メール</title>
</head>

<?php
    /*
        PHPMailerの必要なファイルをインポート
        「PHPMailer」フォルダの中にある3つのファイルを読み込む

        1. PHPMailer.php
            PHPでメール送信機能の基盤になるPHPファイル
        2. Exception.php
            PHPで何か予期できないエラーが起こった時に実行されるファイル
        3. STMP.php
            メールを送信する時に必要なメールサーバーに接続するためのPHP
            今回はメールを送信するだけなので、SMTPサーバーというメール送信のためのサーバーにアクセス
    */
    require('PHPMailer/src/PHPMailer.php');
    require('PHPMailer/src/Exception.php');
    require('PHPMailer/src/SMTP.php');

    // PHPMailerを使う設定
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    /*
        文字化け防止のために、言語と文字コードを指定
        PHPでメールを送信する時に日本語の文字コード「UTF-8」で送信します。
    */
    mb_language('ja');
    mb_internal_encoding('UTF-8');


    /*
        入力されたデータの取得と機能を作る
        今回は「contact_info」という名前でプログラムをひとくくりにまとめています。

        1. 入力された内容を一時的に保存する変数を設定
            $name : 名前
            $address : 住所
            $tel : 電話番号
            $to : 送信先のメールアドレス
            $contact_type : 希望の連絡方法[電話][メール][どちらでも]
            $contents : 入力されたお問い合わせの文章

            $client_message : お問い合わせした方に返すメールの文章
            $owner_message : オーナーに問い合わせがあったことを知らせる文章

        2. 入力されたデータを変数に保存する機能と返信する文章の生成
            メソッドと呼ばれる機能をまとめたものを作成します。
            今回は「入力データのセット」と「セットした内容を取得」する2つのメソッドを作成

            - set_info()
                入力されたデータをそれぞれの変数に保存します。
                さらに、入力されたデータから返信する文章を顧客用とオーナー用に2つ作成

            - get_info()
                上記の「set_info()」でセットした変数の中身を取得する
    */
    class contact_info {

        /*
            入力された内容を一時的に保存する変数を設定
        */
        private $name = null; // 名前[入力データ]
        private $adress = null; // 住所[入力データ]
        private $tel = null; // 電話番号[入力データ]
        private $to = null; // メールアドレス[入力データ]
        private $contact_type = ''; // 希望の連絡方法[入力データ]
        private $contents = null; // 問い合わせ内容[入力データ]
        private $client_message = null; // お客様に送るメッセージ
        private $owner_message = null; // オーナーに送るメッセージ


        /*
            入力されたデータを変数に保存する機能と返信する文章の生成
        */
        public function set_info() {
            // 入力データの受け取り
            $this -> name = htmlspecialchars($_POST['name'], ENT_QUOTES) . '様';
            $this -> address = htmlspecialchars($_POST['address'], ENT_QUOTES);
            $this -> tel = htmlspecialchars($_POST['tel'], ENT_QUOTES);
            $this -> to = htmlspecialchars($_POST['mail'], ENT_QUOTES);
            $this -> contact_type = htmlspecialchars($_POST['type'], ENT_QUOTES);
            $this -> contents = htmlspecialchars($_POST['contents'], ENT_QUOTES);

            /*
                希望の連絡先をテキストに直す
                0: 電話希望
                1: メール希望
                2: どちらでも可
            */
            $main_contact_type = '電話・メールどちらでも可';
            if($this -> contact_type == '0') {
                $main_contact_type = '電話';
            } else if($this -> contact_type == '1') {
                $main_contact_type = 'メール';
            }

            // HTMLメールのために改行コードを修正する
            $html_format_contents = nl2br($this -> contents);

            // お客様へ送るメッセージ本文
            $this -> client_message =
                                    '<html><body>'
                                    .'<p>'.$this -> name.'</p>'
                                    .'<p>お問い合わせありがとうございます。</p>'
                                    .'<p>下記の内容でメールを送信しました。</p>'
                                    .'<hr>'
                                    .'<p>[名前]<br>'.$this -> name.'</p>'
                                    .'<p>[住所]<br>'.$this -> address.'</p>'
                                    .'<p>[電話番号]<br>'.$this -> tel.'</p>'
                                    .'<p>[メールアドレス]<br>'.$this -> to.'</p>'
                                    .'<p>[取りやすい連絡方法]<br>'.$main_contact_type.'</p>'
                                    .'<p>[お問い合わせ内容]<br>'.$html_format_contents.'</p>'
                                    .'<hr>'
                                    .'<p>お問い合わせの内容を確認次第こちらのメールアドレスにご連絡させていただきます。</p>'
                                    .'<p>もうしばらくお待ちください。</p>'
                                    .'</body></html>';


            // オーナーへ送るメッセージ本文
            $this -> owner_message =
                                    '<html><body>'
                                    .'<p>'.$this -> name.'からお問い合わせがありました。</p>'
                                    .'<hr>'
                                    .'<p>[名前]<br>'.$this -> name.'</p>'
                                    .'<p>[住所]<br>'.$this -> address.'</p>'
                                    .'<p>[電話番号]<br>'.$this -> tel.'</p>'
                                    .'<p>[メールアドレス]<br>'.$this -> to.'</p>'
                                    .'<p>[取りやすい連絡方法]<br>'.$main_contact_type.'</p>'
                                    .'<p>[お問い合わせ内容]<br>'.$html_format_contents.'</p>'
                                    .'<hr>'
                                    .'</body></html>';
        }

        /*
            上記の「set_info()」でセットした変数の中身を取得する
        */
        public function get_info($data_name) {
            return $this -> $data_name;
        }
    }



    /*
        上記の「contact_info」の中にあるプログラムを使えるようにする(インスタンス作成)

        new contact_info()
            これで「contact_info」の中にあるプログラムを使えるようにします。

        $info
            「contact_info」の中のプログラムを使用する際に、今後$infoで「contact_info」にアクセスができます。
    */
    $info = new contact_info();


    /*
        「contact_info」の中にある「set_info()」のメソッドを呼び出す

        呼び出し方は、「contact_info」を意味する$infoでアクセスします。

        $info -> set_info()
        意味: contact_info の中の set_info()を呼び出す
    */
    $info -> set_info();


    /*
        PHPMailerのプログラムを使えるようにする(インスタンス作成)

        new PHPMailer()
            これで「PHPMailer」の中にあるプログラムを使えるようにします。

        $mailer
            「PHPMailer」の中のプログラムを使用する際に、今後$mailerで「PHPMailer」にアクセスができます。
    */
    $mailer = new PHPMailer(true);


    // 文字化け防止のため、PHPMailerの文字コードを指定
    $mailer -> CharSet = 'utf-8';


    /*
        メール送信プログラム
        ここから、実際に実行される処理を書いています。
    */
    try {
        /*
            送信設定
            Gmailのサーバーを経由して送信する

            Gmaileのサーバーにアクセスする設定とオーナーのアドレスとパスワードを設定します。
        */
        $mailer -> isSMTP();
        $mailer -> isHTML(true);
        $mailer -> Host = 'smtp.gmail.com';
        $mailer -> SMTPAuth = true;
        $mailer -> Username = 'aya.012314@gmail.com'; // 七枝さんのアドレス
        $mailer -> Password = 'bglwzdgxvhxjvjvq'; // 七枝さんが生成した、Webアプリ用のGmailアカウントのパスワード
        $mailer -> SMTPSecure = 'tls';
        $mailer -> Port = 587;


        // 送信元: 七枝さんのアドレス
        $mailer -> setFrom(
            $mailer -> Username,
            '七枝'
        );

        // 送信先: 入力データのアドレスと名前
        $mailer -> addAddress(
            $info -> get_info('to'),
            $info -> get_info('name')
        );

        // 件名
        $mailer -> Subject = 'お問い合わせ';

        // お客様へ送る本文をセット
        $mailer -> Body = $info -> get_info('client_message');

        // お客様へメールを送信
        $mailer -> send();


        // 送信先のリセット
        $mailer -> ClearAddresses();


        // 送信先: 七枝さん(自分から自分にメールを送信)
        $mailer -> addAddress(
            $mailer -> Username,
            '七枝'
        );

        // 件名
        $mailer -> Subject = 'お問い合わせがありました。';

        // オーナーへ送る本文をセット
        $mailer -> Body = $info -> get_info('owner_message');

        // オーナーへ問い合わせのメールを送信
        $mailer -> send();

    } catch(Exception $e) {
        // 何かしらのエラーをキャッチ
    }
?>

<body>
    <main>
        <h1>メール</h1>
        <div class="recieve_info">
            <ul>
                <li>
                    名前: <?= $info -> get_info('name') ?>
                </li>
                <li>
                    住所: <?= $info -> get_info('address') ?>
                </li>
                <li>
                    電話: <?= $info -> get_info('tel') ?>
                </li>
                <li>
                    メール: <?= $info -> get_info('to') ?>
                </li>
                <li>
                    連絡方法: <?= $info -> get_info('contact_type') ?>
                </li>
                <li>
                    内容: <?= $info -> get_info('contents') ?>
                </li>
            </ul>
        </div>
    </main>
</body>

</html>
