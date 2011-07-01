<form method="post" action="">
<?=bitrix_sessid_post()?>
<?php
	$sTableID = $this->getCode();
	$lAdmin = new CAdminList($sTableID);

	$aHeaders = array(
		array(
			'id' 		=> 'name',
			'content' 	=> 'Название пункта меню',
			'default'	=> true,
		),
		array(
			'id' 		=> 'dir',
			'content' 	=> 'Ссылка на папку на сервере',
			'default'	=> true,
		),
		array(
			'id' 		=> 'rights',
			'content' 	=> 'Отображать для групп пользователей',
			'default'	=> true,
		),
	);

	$lAdmin->AddHeaders($aHeaders);

	$aOriginalItems = $this->getOriginalMenu();
	foreach( $aOriginalItems as $sKey => $aItem ){

		if( !isset( $aOptionsRaw['items']['system'][ $aItem['url'] ] ) ) $aOptionsRaw['items']['system'][ $aItem['url'] ] = array(2);

		$row =& $lAdmin->AddRow($sKey, $aItem);
		$row->AddViewField("name", 'Системный пункт меню <br /><b>'.$aItem['text'].'</b>' );
		$row->AddViewField("dir", $aItem['url'] );
		$row->AddViewField("rights", BitrixGemsHelper::GetUserGroupSelect('FileStructureMenuBuilder[items][system]['.$aItem['url'].'][]', $aOptionsRaw['items']['system'][ $aItem['url'] ] ) );
	}
	if( !empty( $aOptionsRaw['items']['user'] ) ){
		foreach( $aOptionsRaw['items']['user'] as $sUrl => $aOption  ){
			$row =& $lAdmin->AddRow( $sUrl, $aOption );
			$row->AddViewField("name", '<input type="text" style="width:100%;" value="'.htmlspecialchars( $aOption['name'] ).'" name="FileStructureMenuBuilder[items][user]['.$sUrl.'][name]" />' );
			$row->AddViewField("dir", '<input type="text" style="width:100%;" value="'.htmlspecialchars( $sUrl ).'" name="FileStructureMenuBuilder[items][user]['.$sUrl.'][dir]" />' );
			$row->AddViewField("rights", BitrixGemsHelper::GetUserGroupSelect('FileStructureMenuBuilder[items][user]['.$sUrl.'][rights][]', $aOption['rights'] ) );
		}
	}
	//Ну и парочку для создания новых уровней
	for( $i=1; $i<4; $i++ ){
		$row =& $lAdmin->AddRow( 'new'.$i, array() );
		$row->AddViewField("name", '<input value="Новый уровень меню '.$i.'" type="text" style="width:100%;" value="" name="FileStructureMenuBuilder[items][user][new'.$i.'][name]" />' );
		$row->AddViewField("dir", '<input type="text" style="width:100%;" value="" name="FileStructureMenuBuilder[items][user][new'.$i.'][dir]" />' );
		$row->AddViewField("rights", BitrixGemsHelper::GetUserGroupSelect('FileStructureMenuBuilder[items][user][new'.$i.'][rights][]', $aOptionsRaw['items']['user'][ $sUrl ]['rights'] ) );
	}

	$lAdmin->AddFooter(
		array(
			array("title"=>"", "value"=>'<input type="submit" value="Сохранить" />'),
		)
	);

	$lAdmin->CheckListMode();
	$lAdmin->DisplayList();
?>
</form>