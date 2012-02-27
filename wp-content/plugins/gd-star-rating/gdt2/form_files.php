<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Export Own Templates", "gd-star-rating"); ?></th>
    <td>
        <?php _e("This will export all templates you created into a file.", "gd-star-rating"); ?><br />
        <div class="inputbutton"><a href="<?php echo STARRATING_URL; ?>export.php?ex=t2"><?php _e("Export", "gd-star-rating"); ?></a></div>
        <div class="gdsr-table-split"></div>
        <?php _e("Exported file will contain names, descriptions and contents of templates. File will be in CSV format.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Export All Templates", "gd-star-rating"); ?></th>
    <td>
        <?php _e("This will export all templates and their dependencies.", "gd-star-rating"); ?><br />
        <div class="inputbutton"><a href="<?php echo STARRATING_URL; ?>export.php?ex=t2full"><?php _e("Export", "gd-star-rating"); ?></a></div>
        <div class="gdsr-table-split"></div>
        <?php _e("Exported file will contain full structure of the database table. File will be in CSV format. This is useful if you need to transfer everything the way it is to another website.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Import Own Templates", "gd-star-rating"); ?></th>
    <td>
        <?php _e("Use this to import templates you made. File must be generated with first export tool.", "gd-star-rating"); ?><br />
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="gdsr_t2_import_own" /><br />
            <input class="inputbutton" type="submit" name="gdsr_t2imp_own" value="<?php _e("Import", "gd-star-rating"); ?>" />
        </form>
        <div class="gdsr-table-split"></div>
        <?php _e("Once imported, these templates will use default dependencies.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Import All Templates", "gd-star-rating"); ?></th>
    <td>
        <?php _e("Use this to import all templates. File must be generated with second export tool.", "gd-star-rating"); ?><br />
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="gdsr_t2_import_all" /><br />
            <input class="inputbutton" type="submit" name="gdsr_t2imp_all" value="<?php _e("Import", "gd-star-rating"); ?>" />
        </form>
        <div class="gdsr-table-split"></div>
        <?php _e("Before import, all existing templates will be deleted. All dependencies from the file will be preserved.", "gd-star-rating"); ?>
    </td>
</tr>
</tbody></table>
