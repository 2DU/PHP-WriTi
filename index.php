<?php
    $lang_file = array(
        'insert' => 'Insert',
        'login' => 'Login',
        'edit' => 'Edit',
        'record' => 'Record',
        'password' => 'Password',
    );

    function html_load($data = '404 Error', $head = '') {
        $main_html =    '<!DOCTYPE html>
                        <html>
                            <head>
                                <meta charset="utf-8">
                                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                <meta name="viewport" content="width=device-width, initial-scale=1">
                                <title>Test</title>
                                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
                                <style>
                                    body {
                                        padding-top: 50px;
                                    }

                                    .starter-template {
                                        padding-top: 20px;
                                    }

                                    textarea {
                                        border: solid 1px #aaa;
                                        border-radius: 3px;
                                        width: 100%;
                                        padding: 10px;
                                    }

                                    button {
                                        border: 1px solid #aaa;
                                        background: white;
                                        padding-top: 5px;
                                        padding-left: 10px;
                                        border-radius: 3px;
                                        padding-right: 10px;
                                        padding-bottom: 5px;
                                    }

                                    input {
                                        padding: 5px;
                                        border: 1px solid #aaa;
                                        border-radius: 3px;
                                    }
                                </style>
                                '.$head.'
                            </head>
                            <body>
                                <nav class="navbar navbar-inverse navbar-fixed-top">
                                    <div class="container">
                                        <div class="navbar-header">
                                            <a class="navbar-brand" href="'.url_fix().'">Test</a>
                                        </div>
                                        <ul class="nav navbar-nav">
                                            <li><a href="'.url_fix('?action=list').'">'.load_lang('record').'</a></li>
                                        </ul>
                                    </div>
                                </nav>
                                <div class="container">
                                    <div class="starter-template">
                                        '.$data.'
                                    </div>
                                </div>
                            </body>
                        </html>';

        $main_html = preg_replace('/\n +/', "\n", $main_html);
                    
        return $main_html;
    }

    function redirect($url = '') {
        return '<meta http-equiv="refresh" content="0; url='.$_SERVER['PHP_SELF'].$url.'">';
    }

    function url_fix($url = '') {
        return $_SERVER['PHP_SELF'].$url;
    }

    function render($data) {
        $data = preg_replace('/\n/', '<br>', $data);

        $data = preg_replace('/\*\*((?:(?!\*\*).)+)\*\*/', '<b>$1</b>', $data);
        $data = preg_replace('/\*((?:(?!\*).)+)\*/', '<i>$1</i>', $data);

        $data = preg_replace('/__((?:(?!__).)+)__/', '<u>$1</u>', $data);
        $data = preg_replace('/_((?:(?!_).)+)_/', '<i>$1</i>', $data);

        $data = preg_replace('/~~((?:(?!~~).)+)~~/', '<s>$1</s>', $data);

        return $data;
    }

    function load_lang($data) {
        global $lang_file;

        if($lang_file[$data]) {
            return $lang_file[$data];
        } else {
            return $data;
        }
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
            echo html_load( '<form method="post">
                                <input name="pw" type="password" placeholder="'.load_lang('password').'">
                                <button>'.load_lang('insert').'</button>
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
                echo html_load( '<form method="post">
                                    <input name="pw" type="password" placeholder="'.load_lang('password').'">
                                    <button>'.load_lang('login').'</button>
                                </form>');
            } else {
                if($user_data[0]['data'] == $_POST["pw"]) {
                    $_SESSION['pw'] = 'OK';
                }
                                
                echo redirect();
            }
        } else {
            switch($_GET['action']) {
                case '':
                    $select = $conn -> query('select data from data where date = "'.date('Y-m-d').'"');
                    $select -> execute();
                    $daily_data = $select -> fetchAll();
                    if(!$daily_data) {
                        if($_SERVER['REQUEST_METHOD'] != 'POST') {
                            echo html_load( '<form method="post">
                                                <textarea rows="25" cols="100" name="data"></textarea>
                                                <br>
                                                <button>'.load_lang('insert').'</button>
                                            </form>');
                        } else {
                            $insert = $conn -> prepare('insert into data (data, date) values (:data, "'.date('Y-m-d').'")');
                            $insert -> bindParam(':data', $_POST['data']);
                            $insert -> execute();

                            echo redirect();
                        }
                    } else {
                        echo html_load( '<h1>'.date('Y-m-d').'</h1> <a href="'.url_fix('?action=edit').'">('.load_lang('edit').')</a>
                                        <br>
                                        <br>'
                                        .render($daily_data[0]['data']));
                    }
                    
                    break;
                case 'edit':
                    if($_SERVER['REQUEST_METHOD'] != 'POST') {
                        $select = $conn -> query('select data from data where date = "'.date('Y-m-d').'"');
                        $select -> execute();
                        $daily_data = $select -> fetchAll();
                        if($daily_data) {
                            echo html_load( '<form method="post">
                                                <textarea rows="25" cols="100" name="data">'.$daily_data[0]['data'].'</textarea>
                                                <br>
                                                <button>'.load_lang('insert').'</button>
                                            </form>');
                        } else {
                            echo redirect();
                        }
                    } else {
                        $insert = $conn -> prepare('update data set data = :data where date = "'.date('Y-m-d').'"');
                        $insert -> bindParam(':data', $_POST['data']);
                        $insert -> execute();

                        echo redirect();
                    }

                    break;
                case 'list':
                    $select = $conn -> query('select date from data');
                    $select -> execute();
                    $all_data = $select -> fetchAll();
                    if($all_data) {
                        $list = '';
                        foreach ($all_data as &$data) {
                            $list = $list.'<li><a href="'.url_fix('?action=view&date='.$data['date']).'">'.$data['date'].'</a></li>';
                        }

                        echo html_load($list);
                    } else {
                        echo redirect();
                    }

                    break;
                case 'view':
                    if($_GET['date']) {
                        $select = $conn -> query('select data from data where date = "'.date('Y-m-d', strtotime($_GET['date'])).'"');
                        $select -> execute();
                        $daily_data = $select -> fetchAll();
                        if($daily_data) {
                            echo html_load( '<h1>'.date('Y-m-d', strtotime($_GET['date'])).'</h1>
                                            <br>
                                            <br>'
                                            .render($daily_data[0]['data']));
                        } else {
                            echo redirect();
                        }
                    } else {
                        echo redirect();
                    }

                    break;
                default:
                    echo redirect();
            }
        }
    }
?>