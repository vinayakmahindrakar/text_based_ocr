<?php
$base_url = 'http://localhost/ocr_test/';
?>
<html>
<head>
    <title>OCR Demo</title>
    <script src="<?php echo $base_url; ?>js/jquery-3.3.1.js?<?php echo rand(); ?>"></script>
    <script type="text/javascript">
        var base_url = "<?php echo $base_url;?>";
    </script>
    <script src="<?php echo $base_url; ?>js/custom.js?<?php echo rand(); ?>"></script>
    <style>
        body {
            max-width: 1200px;
            margin: 0 auto
        }
		#canvas{
			max-height: 312px !important;
			overflow-y: scroll;
		}
        .cls_img {
            border: solid 1px blue;
            width: 100px;
            height: 100px;
            cursor: pointer;
        }

        div.main {
            border: solid 1px green;
            width: 100%;
        }

        div.left {
            border: solid 1px yellow;
            width: 100%;
            float: left;
        }

        div.right {
            border: solid 1px yellow;
            width: 100%;
            float: left;
            text-align: left;
        }

        #filename_x {
            width: 400px;
        }
    </style>
</head>
<body>
<h1>OCR Demo</h1>
<table width="100%" border="0" align="center" cellspacing="5" cellpadding="5">
    <tr>
        <td>
            <input type="button" id="do_ocr" value="DO OCR">
            &nbsp;<input type="text" id="filename_x" name="filename_x" value=""/>
            &nbsp;<input type="text" id="filename" name="filename" value=""/>
            <input type="button" id="search_text" value="Start Search" disabled>
			<input type="button" id="open_img" value="View Image" disabled>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
</table>
<div class="main">
    <div class="left" id="canvas" name="canvas">Loading Files...</div>
    <div class="right" id="output" name="output">Result</div>
</div>
</body>
</html>