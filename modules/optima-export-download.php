<?php
    ini_set('memory_limit', '-1');

    $od = strtotime($_POST["Date_Year"].'-'.$_POST["Date_Month"].'-01');
    $do = strtotime($_POST["Date_Year"].'-'.$_POST["Date_Month"].'-01'." +1 month");

    $date_from = strtotime(date("Y-m-1",$od));
    $date_to = strtotime(date("Y-m-1",$do))-4*60*60;
//    echo $date_from."\n";
//    echo $date_to."\n";
    $divisionid = 1;
    if (isset($_POST["division"])) $divisionid = intval($_POST["division"]);

    $faktury = $DB->GetAll("select * from documents where divisionid=".$divisionid." and type = 1 and cdate between ".$date_from." and ".$date_to);
    foreach ($faktury as $key => $faktura) {
	$where = " where ic.docid = ".$faktura["id"];
	$from = " from invoicecontents ic left join taxes t on (t.id = ic.taxid) ";
	$faktury[$key]["customer"] = $LMS->GetCustomer($faktura["customerid"]);
	$icquery = "select ic.*, t.value as taxvalue, (ic.value*ic.count) as value_brutto, round((ic.value*ic.count)/(1+t.value/100),2) as value_netto".$from.$where;
//	var_dump($icquery);
	$faktury[$key]["content"] = $DB->GetAll($icquery);
	$faktury[$key]["brutto"] = strval($DB->GetOne("select sum(ic.value*ic.count) ".$from.$where));
	$faktury[$key]["brutto_8"] = strval($DB->GetOne("select sum(ic.value*ic.count) ".$from.$where." and t.value = 8"));
	$faktury[$key]["brutto_23"] = strval($DB->GetOne("select sum(ic.value*ic.count) ".$from.$where." and t.value = 23"));
	$faktury[$key]["netto_8"] =  round($faktury[$key]["brutto_8"]  / 1.08 , 2);
	$faktury[$key]["netto_23"] = round($faktury[$key]["brutto_23"] / 1.23 , 2);
	$faktury[$key]["netto"] = $faktury[$key]["netto_8"] + $faktury[$key]["netto_23"];
	$faktury[$key]["vat"] = $faktury[$key]["brutto"] - $faktury[$key]["netto"];
    }
//    var_dump($faktury);
//	die(0);
//    $kpkws = $DB->GetAll("select * from documents where type = 2 and cdate between ".$date_from." and ".$date_to." order by cdate ASC");
//    foreach ($kpkws as $key => $kpkw) {
//	$kpkws[$key]["customer"] = $LMS->GetCustomer($kpkw["customerid"]);
//	$kpkws[$key]["content"] = $DB->GetAll("select * from receiptcontents where docid = ".$kpkw["id"]);
//    }

    $simps_values_q = "select sum(value) as value, date from cashimport where sourceid = 1 and date between ".$date_from." and ".$date_to." group by date";
    $simps_values = $DB->GetAll($simps_values_q);
    $simps = $DB->GetAll("select * from cashimport where sourceid = 1 and date between ".$date_from." and ".$date_to." order by date ASC");
//    foreach ($simps as $key => $simp) {
//	$simps[$key]["customer"] = $LMS->GetCustomer($simp["customerid"]);
//    }


//    $SMARTY->assign('kpkws', $kpkws);
    $SMARTY->assign('simps', $simps);
    $SMARTY->assign('simps_values', $simps_values);
    $SMARTY->assign('faktury', $faktury);
    $layout['pagetitle'] = 'Export symfonia';

    header('Content-type: text/plain; charset=utf-8');
    header('Content-disposition: attachment; filename="export-sp2-'.$divisionid.'.xml"');
    $SMARTY->display('optima-export/optima-export-download.html');
?>