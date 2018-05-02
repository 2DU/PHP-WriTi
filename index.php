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
                
    switch($_GET["action"]) {
        case '':
            if($_SERVER['REQUEST_METHOD'] != 'POST') {
                echo html_load('<form method="post">
                                    <button>Test</button>
                                </form>');
            } else {
                echo html_load('Hello, Dark!');
            }
            
            break;
        default:
            echo html_load();
    }
?>