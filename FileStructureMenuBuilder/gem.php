<?php
/**
 * Перестройка файлового меню Битрикс.
 *
 * @author		Vladimir Savenkov <iVariable@gmail.com>
 *
 */
class BitrixGem_FileStructureMenuBuilder extends BaseBitrixGem{

	protected $aGemInfo = array(
		'GEM'			=> 'FileStructureMenuBuilder',
		'AUTHOR'		=> 'Владимир Савенков',
		'AUTHOR_LINK'	=> 'http://bitrixgems.ru/',
		'DATE'			=> '22.02.2011',
		'VERSION'		=> '0.1',
		'NAME' 			=> 'FileStructureMenuBuilder',
		'DESCRIPTION' 	=> "Перестройка стандартного файлового меню Битрикс в зависимости от пользователя. Добавление новых уровней, скрытие существующих.",
		'CHANGELOG'		=> '',
		'REQUIREMENTS'	=> '',
	);

	public function event_main_OnBeforeProlog_PrepareCache(){

		$aMenu = $this->getConfiguredMenuLevels();

		if( !empty( $aMenu ) ){
			global $BX_CACHE_DOCROOT,$CACHE_MANAGER;
			foreach( $aMenu as $aConfig ){
				$cacheId = "b_lang".md5("..".serialize(array('LID' => $aConfig['LID'])));

				$CACHE_MANAGER->Read(CACHED_b_lang, $cacheId, "b_lang");
				$CACHE_MANAGER->Set($cacheId, $aConfig);

				$BX_CACHE_DOCROOT[$aConfig['LID']] = $aConfig['ABS_DOC_ROOT'];
			}
		}
	}
	
	public function event_main_OnBuildGlobalMenu_RebuildFileMenu( $mRootMenu, &$mItems ){
		global $adminMenu, $USER;
		$aConfig = $this->getOptions();
		$aMenu = $this->getConfiguredMenuLevels();
		$aUG = $USER->GetUserGroupArray();
		foreach( $mItems as $iKey => &$aItem ){
			if( $aItem['section'] == 'fileman' ){

				$this->aOriginalMenu = $aItem['items'];

				foreach( $aItem['items'] as $iKey => $aValue ){
					if( isset( $aConfig['items']['system'][ $aValue['url'] ] ) ){
						$aUGIntersect = array_intersect( $aUG, $aConfig['items']['system'][ $aValue['url'] ] );
						$bHasAccess = !empty( $aUGIntersect );
						if( !$bHasAccess ){
							unset( $aItem['items'][$iKey] );
						}
					}
				}

				if( !empty( $aMenu ) ){
					foreach( $aMenu as $sLID => $aLevelConfig ){
						$aUGIntersect = array_intersect( $aUG, @$aConfig['items']['user'][ $sLID ]['rights'] );
						$bHasAccess = !empty( $aUGIntersect );
						if( $bHasAccess ) $aItem['items'][] = __add_site_logical_structure($aLevelConfig, $adminMenu);
					}
				}
			}
		}
	}
	
	public function needAdminPage(){
		return true;
	}

	protected $aOriginalMenu = array();
	public function getOriginalMenu(){
		return $this->aOriginalMenu;
	}

	/**
	 * Дефолтное сохранение опций.
	 * @param  $aOptions
	 * @return int
	 */
	protected function setOptions( $aOptions ){
		if( !empty( $aOptions['items']['user'] ) ){
			foreach( $aOptions['items']['user'] as $sKey => $aValue ){
				if( empty( $aValue['dir'] ) || empty( $aValue['name'] ) ){
					unset( $aOptions['items']['user'][$sKey] );
					continue;
				}
				unset( $aOptions['items']['user'][$sKey] );
				$aOptions['items']['user'][ $aValue['dir'] ] = $aValue;
			}
		}
		return parent::setOptions( $aOptions );
	}
	
	protected function getConfiguredMenuLevels(){
		$aOptions = $this->getOptions();
		$aResult = array();
		if( !empty( $aOptions['items']['user'] ) ){
			foreach( $aOptions['items']['user'] as $sUrl => $aConfig ){
				$aResult[ $sUrl ] = array(
					'ID' => $sUrl ,
					'LID' => $sUrl,
					'SORT' => '1',
					'DEF' => 'N',
					'ACTIVE' => 'Y',
					'NAME' => $aConfig['name'],
					'DIR' => '/',
					'FORMAT_DATE' =>  'DD.MM.YYYY',
					'FORMAT_DATETIME' =>  'DD.MM.YYYY HH:MI:SS',
					'CHARSET' =>  'UTF-8',
					'LANGUAGE_ID' =>  'ru',
					'ABS_DOC_ROOT' =>  $sUrl,
					'DOC_ROOT' =>  $sUrl,
					'DOMAIN_LIMITED' =>  'Y',
					'SERVER_NAME' =>  '',
					'SITE_NAME' =>  'BitrixGems FileStructureMenuBuilder',
					'EMAIL' =>  '' ,
					'DOMAINS' =>  'bitrixgems.ru',
					'SITE_URL' =>  'http://bitrixgems.ru' ,
				);
			}
		}
		return $aResult;
	}

}
?>