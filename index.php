<?php
    function html_load($data = '404 Error', $head = '') {
        $main_html = '<html>
                        <head>
                            '.$head.'
                        </head>
                        <body>
                            <div id="main">
                                '.$data.'
                            </div>
                        </body>
                    </html>';
                    
        return $main_html;
    }

    function redirect($url = '') {
        if($url = '') {
            $url = $_SERVER['REQUEST_URI'];
        }

        return '<meta http-equiv="refresh" content="0; url='.$url.'">';
    }

    $conn = new PDO('sqlite:data.db');
    session_start();

    $create = $conn -> prepare('create table if not exists data(data text, date text)');
    $create -> execute();

    $create = $conn -> prepare('create table if not exists set_data(id text, data text)');
    $create -> execute();
                
    $select = $conn -> query('select data from set_data where id = "pw"');
    $select -> execute();
    $user_data = $select -> fetchAll();
    if(!$user_data) {
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo html_load('<form method="post">
                                <input name="pw">
                                <button>InSert</button>
                            </form>');
        } else {
            $insert = $conn -> prepare('insert into set_data (id, data) values ("pw", :pw)');
            $insert -> bindParam(':pw', $_POST["pw"]);
            $insert -> execute();

            echo redirect();
        }
    } else {
        if(!isset($_SESSION['pw'])) {
            if($_SERVER['REQUEST_METHOD'] != 'POST') {
                echo html_load('<form method="post">
                                    <input name="pw">
                                    <button>Login</button>
                                </form>');
            } else {
                if($user_data[0]['data'] == $_POST["pw"]) {
                    $_SESSION['pw'] = 'OK';
                }
                                
                echo redirect();
            }
        } else {
            switch($_GET["action"]) {
                case '':
                    $select = $conn -> query('select data from data where date = "'.date("Y-m-d").'"');
                    $select -> execute();
                    $daily_data = $select -> fetchAll();
                    if(!$daily_data) {
                        if($_SERVER['REQUEST_METHOD'] != 'POST') {
                            echo html_load('<form method="post">
                                                <textarea rows="25" cols="100" name="data"></textarea>
                                                <br>
                                                <button>InSert</button>
                                            </form>');
                        } else {
                            $insert = $conn -> prepare('insert into data (data, date) values (:data, "'.date("Y-m-d").'")');
                            $insert -> bindParam(':data', $_POST["data"]);
                            $insert -> execute();

                            echo redirect();
                        }
                    } else {
                        echo html_load(date("Y-m-d").'
                                        <br>'
                                        .preg_replace('/\n/', '<br>', $daily_data[0]['data']));
                    }
                    
                    break;
                case 'views':
                    echo 'test';
                default:
                    echo html_load();
            }
        }
    }
?>