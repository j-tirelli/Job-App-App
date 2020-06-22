<?php
ignore_user_abort();
ini_set('max_execution_time', 86400);

require "db.php";

if (isset($_POST['formSubmit'])) {

    if ($_POST['dump'] == '') {
        setMessage('"Dump" cannot be empty!', 'error');
    }

    if (!getMessages(false, 'error')) {
        $arr = array_filter(explode("\n", $_POST['dump']), 'strlen');
        foreach ($arr as $key => $value) {
            $k = str_getcsv($value, $_POST['delimiter'], $_POST['enclosure']);
            for ($i = 1; $i <= 18; $i++) {
                if ($_POST['select' . $i]) {
                    $import[$key][$_POST['select' . $i]] = $k[$i - 1];
                }
            }
        }

        $getUser = dbRow("SELECT `record_num` FROM `users` WHERE `username` = '" . mysqli_real_escape_string($dbconn, $_POST['submitter']) . "'", false);
        $_POST['submitter'] = is_array($getUser) ? $getUser['record_num'] : 0;

        $counter = 0;
        $errors = 0;

        foreach ($import as $import_row) {
            $insert_id = dbInsert('csv_import', array(
                'title' => $import_row['title'],
                'flv' => $import_row['flv'],
                'thumb' => $import_row['thumb'],
                'desc' => $import_row['desc'],
                'embed' => $import_row['embed'],
                'keywords' => $import_row['keywords'],
                'lengthsec' => $import_row['length'],
                'paysite' => $import_row['paysite'],
                'pornstars' => $import_row['pornstars'],
                'local' => $import_row['local'],
                'plugurl' => $import_row['plugurl'],
                'default_paysite' => $_POST['default_paysite'],
                'hotlink' => $_POST['ishotlinked'],
                'submitter' => (int)$_POST['submitter'],
				'vr' => (int)$_POST['vr'],
				'jsondata' => json_encode($import_row)
            ));
            if (is_numeric($insert_id)) {
                $counter++;
            } else {
                $errors++;
            }
        }
        setMessage("<strong>$counter videos added to import queue. $errors videos were not added.</strong>", 'info');
        if ($counter > 0) {
            $pid = backgroundProcess("$php_path $basepath/admin/csv_cron.php", "csv_log.txt");
            setMessage("CSV Importer has been started as PID: $pid");
        }
        header("Location: $_SERVER[REQUEST_URI]");
        exit();
    }
}

if ($_POST['delList']) {
    foreach ($_POST['delList'] as $i) {
        if (is_numeric($i)) {
            dbQuery("DELETE FROM `csv_import_list` WHERE `record_num` = '$i'");
        }
    }
    setMessage('Your preset has been removed');
    header("Location: $_SERVER[REQUEST_URI]");
    exit();
}

entities_walk($_POST);
?>

<? require "header.php"; ?>

<script>
    $(document).ready(function () {
        $("#saveAsPreset").click(function (e) {
            e.preventDefault();
            $("#presetSave").toggle("slow");
        });
        $("#managePresets").colorbox({width: "800px", height: "300px", iframe: false});
        $("#savePreset").click(function (e) {
            e.preventDefault();

            if ($('input[name=name]').val().length > 0) {
                if ($('#import-format-selects select option[value!=""]:selected').length > 0) {
                    $.ajax({
                        url: '<?php echo $basehttp; ?>/admin/savePreset.php',
                        dataType: 'json',
                        type: 'GET',
                        data: $('#form-import').serialize(),
                        success: function (data) {
                            if (data['record_num']) {
                                $('#preset').append('<option value="' + data['record_num'] + '">' + data['name'] + '</option>').attr('selected', 'selected');
                                $('#preset').val(data['record_num']).find('option[value="' + data['record_num'] + '"]').attr('selected', 'selected');
                            }
                            $("#saveSuccess").show();
                            $("#saveFail").hide();
                            $("#saveAsPreset").text('Edit Preset');
                        }
                    });
                } else {
                    $("#saveSuccess").hide();
                    $("#saveFail").show();
                }
            } else {
                alert("Please provide preset's name.");
            }

            return false;
        });
        $("#preset").change(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?php echo $basehttp; ?>/admin/loadPreset.php',
                dataType: 'json',
                type: 'GET',
                data: 'id=' + $('#preset').val(),
                success: function (data) {
                    $.each(data, function (k, v) {
                        $('#import-format-selects select[name="' + k + '"]').val("").find('option[selected="selected"]').removeAttr("selected");
                        $('#import-format-selects select[name="' + k + '"]').trigger('update');
                    });
                    $.each(data, function (k, v) {
                        $('#import-format-selects select[name="' + k + '"]').val(v).find('option[value="' + v + '"]').attr('selected', 'selected');
                        $('#import-format-selects select[name="' + k + '"]').val(v).trigger('update');
                    });
                    $('input[name=name]').val(data['name']);
                    $("#saveAsPreset").text('Edit Preset');
                }
            });
            return false;
        });
    });
</script>
<div class="content-page">

    <div class="header-area">
        <div class="breadcrumbs">  
            <a href="index.php">Admin Home</a>          
            <span><a href="csv_import.php">CSV Import</a></span>  
        </div>
    </div>

    <div class="content-outer">  

        <h2>CSV<strong>Import</strong></h2>

        
        <div class="notification info"><strong>Import may take a while on hosted/hotlinked videos, as it has to download the video from the source server in order to make thumbnails. If you are importing from a sponsor or http server, you must specify the video file location with the 'file_url' field. If you are importing from the local drive, you must enter the *FULL LINUX PATH* to the file. If it's not working - you most likely do not have the correct path!</strong></div>
<div class="notification info"><strong>Alternate Languages</strong>: a base title description MUST be filled out. the title, description and keyword fields are imported into the database in the base database language (which is invariably english, regardless of what you have set in the configuration). If you switch the languages on the front end of your site, it will always pull this english version if there is no alternate language text specified for this particular piece of content. If you are targetting your site to a single specific language, please import that as title/description/keywords in that language even if it's not english. If you require further clarification on this, please contact support.</div>

        <div class="content-inner">
			
            <? echo getMessages(); ?>
			<? $langs = dbQuery("SELECT * FROM languages ORDER BY name ASC",false); ?>
			
            <form method="POST" action="" id="form-import" class="form" novalidate autocomplete="off">
                <table class="pagetable">
                    <tbody>
                        <tr>
                            <td>Import Format:</td>
                            <td>
                                <div class="row columnized-input" id="import-format-selects">
                                    <?php for ($i = 1; $i <= 18; $i++) { ?>
                                        <div class="col-xs-12 col-sm-4 col">
                                            <span class="prefix"><?php echo $i; ?></span>
                                            <select name="select<?php echo $i; ?>">
                                                <option value="">-</option>
                                                <option value="title"<? echo $_POST['select' . $i] == 'title' ? ' selected' : ''; ?>>title</option>
                                                <option value="flv"<? echo $_POST['select' . $i] == 'flv' ? ' selected' : ''; ?>>file_url</option>
                                                <option value="local"<? echo $_POST['select' . $i] == 'local' ? ' selected' : ''; ?>>localfile</option>
                                                <option value="plugurl"<? echo $_POST['select' . $i] == 'plugurl' ? ' selected' : ''; ?>>plugurl</option>
                                                <option value="thumb"<? echo $_POST['select' . $i] == 'thumb' ? ' selected' : ''; ?>>thumb</option>
                                                <option value="desc"<? echo $_POST['select' . $i] == 'desc' ? ' selected' : ''; ?>>desc</option>
                                                <option value="embed"<? echo $_POST['select' . $i] == 'embed' ? ' selected' : ''; ?>>embed</option>
                                                <option value="keywords"<? echo $_POST['select' . $i] == 'keywords' ? ' selected' : ''; ?>>keywords</option>
                                                <option value="length"<? echo $_POST['select' . $i] == 'length' ? ' selected' : ''; ?>>lengthsec</option>
                                                <option value="paysite"<? echo $_POST['select' . $i] == 'paysite' ? ' selected' : ''; ?>>paysite</option>
                                                <option value="pornstars"<? echo $_POST['select' . $i] == 'pornstars' ? ' selected' : ''; ?>>pornstars</option>
												<? if(is_array($langs)) { ?>
													<option value=''></option>
													<option value=''>- Alternate Languages (See note at top) -</option>
													<? foreach($langs as $l) { ?>
														<option value='title_<? echo $l['iso']; ?>' <? echo $_POST['select' . $i] == 'title_'.$l['iso'] ? ' selected' : ''; ?>>title (<? echo $l['iso']; ?>)</option>
														<option value='description_<? echo $l['iso']; ?>' <? echo $_POST['select' . $i] == 'description_'.$l['iso'] ? ' selected' : ''; ?>>description (<? echo $l['iso']; ?>)</option>
														<option value='keywords_<? echo $l['iso']; ?>' <? echo $_POST['select' . $i] == 'keywords_'.$l['iso'] ? ' selected' : ''; ?>>keywords (<? echo $l['iso']; ?>)</option>
													<? } ?>
												<? } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><a href="#" id="saveAsPreset">Save as Preset:</a></td>
                            <td>
                                <div style="display:none;" id="presetSave">
                                    <table class="pagetable">
                                        <tr>
                                            <td class="item submit"><input name="name" type="text" value="" placeholder="Preset name goes here..." /></td>
                                            <td class="item submit"><button name="submit" id="savePreset" class="btn action-save">Save Preset</button></td>
                                        </tr>
                                    </table>
                                    <div class="notification success" id="saveSuccess" style="display:none">Your preset has been saved!</div>
                                    <div class="notification error" id="saveFail" style="display:none">You must select at least one selectbox!</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Import Format Preset:</td>
                            <td>
                                <select name="preset" id="preset">
                                    <option value=""></option>
                                    <?php $rresult = dbQuery("SELECT * FROM `csv_import_list` ORDER BY `name`", false); ?>
                                    <?php foreach ($rresult as $rrow) { ?>
                                        <option value="<?php echo $rrow['record_num']; ?>"><?php echo $rrow['name']; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="suffix"><a id="managePresets" href="manage_presets.php">Manage presets</a></span>
                            </td>
                        </tr>
                        <tr>
                            <td>Delimiter:</td>
                            <td>
                                <select name="delimiter">
                                    <option value="|"<? echo $_POST['delimiter'] == '|' ? ' selected' : ''; ?>>| (pipe)</option>
                                    <option value=","<? echo $_POST['delimiter'] == ',' ? ' selected' : ''; ?>>, (comma)</option>
                                    <option value=";"<? echo $_POST['delimiter'] == ';' ? ' selected' : ''; ?>>; (semi-colon)</option>
                                    <option value="\t"<? echo $_POST['delimiter'] == '\t' ? ' selected' : ''; ?>>\t (tab)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Delimitation Enclosure:</td>
                            <td>
                                <select name="enclosure">
                                    <option value="">none</option>
                                    <option value='"'<? echo $_POST['enclosure'] == '"' ? ' selected' : ''; ?>>" (double-quote)</option>
                                    <option value="'"<? echo $_POST['enclosure'] == "'" ? ' selected' : ''; ?>>' (single-quote)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Default Paysite (Only used if no paysite is provided for video):</td>
                            <td>
                                <select name="default_paysite">
                                    <?php $rresult = dbQuery("SELECT * FROM `paysites` ORDER BY `name`", false); ?>
                                    <?php foreach ($rresult as $rrow) { ?>
                                        <option<?php echo ($_POST['paysite'] == $rrow['record_num']) ? ' selected' : ''; ?> value="<?php echo $rrow['record_num']; ?>"><?php echo $rrow['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Hotlink (Ignored when using embeds or non-flv/mp4 movies):</td>
                            <td>
                                <select name="ishotlinked">
                                    <option value="1"<? echo !isset($_POST['ishotlinked']) || $_POST['ishotlinked'] == '1' ? ' selected' : ''; ?>>Yes</option>
                                    <option value="0"<? echo $_POST['ishotlinked'] == '0' ? ' selected' : ''; ?>>No</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Submitter:</td>
                            <td>
                                <input type="text" name="submitter" id="contentAutocomplete" value="<? echo $_POST['submitter']; ?>" placeholder="Start typing username..." />
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        $("#contentAutocomplete").autocomplete({
                                            source: "search_content.php?type=2",
                                            minLength: 2
                                        });
                                    });
                                </script>
                            </td>
                        </tr>
						<tr>
                            <td>VR Videos:</td>
                            <td>
                                <select name="vr">
                                    <option value='0'>No</option>
									<option value='1'>Yes</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Dump:</td>
                            <td><textarea name="dump" rows="10" required><? echo $_POST['dump']; ?></textarea></td>
                        </tr>
                        <tr class="item submit">
                            <td colspan="2">
                                <input type="hidden" name="formSubmit" value="1">
                                <button type="submit" class="btn action-save">Submit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<?php require "footer.php"; ?>
