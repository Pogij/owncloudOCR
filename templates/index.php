<?php

use OC\Files\Filesystem;

OCP\Util::addscript('images_ocr','performOcr');

if (isset($_['message'])) {
    echo $_['message'];
    exit();
}
?>


<div id="ocrcontent">
    <div id="controls">
        <?php print_unescaped($_['breadcrumb']); ?>
    </div>

    <div id="ocr">

        <div id="image">
            <?php
            $path = Filesystem::getLocalPath($_['path']);
            $img = trim($fullPath . $_['path']);
            echo '<img id="ocrimage" class="ocrimage" src="" alt="' . $img . '" />';
            echo '<input type="hidden" id="imagepath" value="' . $img . '" />';
            ?>
        </div>

        <div id="action" class="middle">
            <?php echo $l->t('Select tesseract language:'); ?> 
            <select id="language">
                    <?php
                    foreach($_['languages'] as $language) {
                        $selected = '';
                        if ($language == "eng") {
                            $selected = ' selected="selected"';
                        }
                        
                        echo '<option value="' . $language . '"' . $selected . '>' . $language . '</option>';
                    }
                    ?>
            </select>
            <br />
            <input type="button" id="ocrButton" name="Perform OCR" value="<?php echo $l->t('Perform OCR&#x00A;reading'); ?>" />
        </div>

        <div id="ocrtext">

            <div id="errorplace"></div>
            <textarea id="textvalue" disabled="disabled">
            </textarea>
            <br />
            <?php echo $l->t('Filename:'); ?> <input type="text" id="filename" maxlength="256" value="" disabled="disabled" />.txt
            <br />
            <input type="button" id="saveButton" name="Save" value="<?php echo $l->t('Save text'); ?>" disabled="disabled" />

        </div>

    </div>
</div>
