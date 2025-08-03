<?php
    $layout['pagetitle'] = trans('Optima Export');
    $SMARTY->assign('divisions', $DB->GetAll('SELECT id, shortname FROM divisions ORDER BY shortname'));
    $SMARTY->display('optima-export/optima-export.html');
?>