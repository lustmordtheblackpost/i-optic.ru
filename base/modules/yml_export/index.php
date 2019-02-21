<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

//
// Очень важно, чтобы объявление класса, у примеру "class moduleabout extends RootModule" было именно такое, какое есть сейчас. 
// Меняется только слово about в случае указанного примера. Также четко следите за регистром.
// Касаемо регистра. В модулях должны совпадать: название каталога, название в классе и название в БД (к примеру везде about или Projects или SuBwAy и тд.)
// 

class moduleyml_export extends RootModule
{
	var $dbinfo = null;
	var $curFilePath = "";
	
	var $gl_dbase_string = "`shop`.";
	
	var $token = "B8000001BD573CB3";
	
	function init( $dbinfo )
	{
		$this->dbinfo = $dbinfo;
		$this->curFilePath = $this->getRootFilePath( __FILE__ );
	}
	
	function processAPI( $test )
	{
		return "123";
	}
	
	function process()
	{
        return $this->get_xml_header().$this->get_xml_shop().$this->get_xml_footer();
	}
	
	function get_xml_header()
	{
		return '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE yml_catalog SYSTEM "shops.dtd"><yml_catalog date="'.date('Y-m-d H:i').'">';
	}
	
	function get_xml_shop()
	{
		global $main, $mysql, $lang, $query, $utils;
		
		$t = "<shop>".
			"<name>".$lang->gp( 7 )."</name>".
			"<company>ИП &quot;НА ТРИ БУКВЫ&quot;</company>".
			"<url>https://".$_SERVER['HTTP_HOST']."/</url>".
			"<phone>+7(495)123-45675</phone>".
			"<platform>OCHKI CMS</platform>".
			"<version>1.1</version>".
			"<agency>Cosmolet</agency>".
			"<email>idpycc@gmail.com</email>".
			"<currencies><currency id=\"RUR\" rate=\"1\"/></currencies>".
			"<categories>";
			
		$main_listings = $main->listings->getListingElementsArraySpec( 22, "`order` DESC, `id` ASC", "", 0, true );
		foreach( $main_listings as $id => $data ) {
			$t .= "<category id=\"".$id."\">".$lang->gp( $data['value'], true )."</category>";
			$rest_listing = $main->listings->getListingElementsArraySpec( 22, "`order` DESC, `id` ASC", "", $id, true );
			foreach( $rest_listing as $child_id => $child_data )
				$t .= "<category id=\"".$child_id."\" parentId=\"".$id."\">".$lang->gp( $child_data['value'], true )."</category>";
		}
		
		$t .= "</categories>".
			"<local_delivery_cost>300</local_delivery_cost>".
			"<offers>";
			
		//$t = iconv( "UTF-8", "WINDOWS-1251", $t );
			
		$vendors = $main->listings->getListingElementsArray( 1, 0, false, '', true );
		$props = $main->properties->getCurrentList();
			
		$a = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar` WHERE `view`=1 AND `vendor`<>330 AND `price`>0 ORDER BY `name` ASC, `id` ASC" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$properties = array();
			$aa = $mysql->mqm( "SELECT * FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_property` WHERE `tovar_id`=".$r['id'] );
			while( $rr = @mysql_fetch_assoc( $aa ) )
				$properties[$rr['id']] = $rr;
				
			$preview = $this->getElementByData( $properties, "prop_id", 51 );
			$preview = $preview ? $preview['value'] : '';
			
			$fullText = $this->getElementByData( $properties, "prop_id", 50 );
			$fullText = strpos( $fullText['value'], "\n" ) ? str_replace( "\n", "", $fullText['value'] ) : $fullText['value'];
			$shortText = $this->getElementByData( $properties, "prop_id", 49 );
			$shortText = strpos( $shortText['value'], "\n" ) ? str_replace( "\n", "", $shortText['value'] ) : $shortText['value'];
			$text = $r['name']."\n".( $shortText ? $shortText : $fullText );
			$text = str_replace( "&quot;", '"', $text );
			$text = str_replace( "&", "&amp;", strip_tags( $text ) );
			$text = str_replace( '"', "&quot;", $text );
			$text = str_replace( "'", "&apos;", $text );
			$text = str_replace( "<", "&lt;", $text );
			$text = str_replace( ">", "&gt;", $text );
			
			$type_prefix = "";
			$market_category = 0;
			switch( $r['r'] ) {
				case 210:
				case "210":
					$type_prefix = "Мойка";
					$market_category = "/Дом и дача/Строительство и ремонт/Сантехника/Кухонные мойки";
					break;
				case 211:
				case "211":
					$type_prefix = "Смеситель";
					$market_category = "/Дом и дача/Строительство и ремонт/Сантехника/Смесители";
					break;
				case 229:
				case "229":
					$type_prefix = "Подстолье";
					break;
				case 212:
				case "212":
					switch( $r['sub_r'] ) {
						case 214:
						case "214":
							$type_prefix = "Рейлинговая система";
							break;
						case 213:
						case "213":
							$type_prefix = "Утилизатор отходов";
							$market_category = "/Бытовая техника/Мелкая техника для кухни/Измельчители пищевых отходов";
							break;
					}
					break;
				case 215:
				case "215":
					switch( $r['sub_r'] ) {
						case 226:
						case "226":
							$type_prefix = "Варочная поверхность";
							$market_category = "/Бытовая техника/Крупная техника для кухни/Встраиваемые рабочие поверхности";
							break;
						case 227:
						case "227":
							$type_prefix = "Вытяжка";
							$market_category = "/Бытовая техника/Крупная техника для кухни/Вытяжки";
							break;
						case 228:
						case "228":
							$type_prefix = "Духовой шкаф";
							$market_category = "/Бытовая техника/Крупная техника для кухни/Встраиваемые духовые шкафы";
							break;
						case 0:
						case "0":
							if( $r['vendor'] == 274 ) {
								$type_prefix = "Посудомоечная машина";
								$market_category = "/Бытовая техника/Крупная техника для кухни/Посудомоечные машины";
							}
							break;
					}
					break;
			}
			
			if( $r['vendor'] == 457 ) {
				$r['name'] .= " (".$r['article'].")";
				if( $r['guid'] )
					$r['guid'] .= " (".$r['article'].")";
			}
			
			$local = "<offer id=\"".$r['id']."\" type=\"vendor.model\" available=\"true\" bid=\"".$r['id']."\">".
				"<url>https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."catalog/show".$r['id']."</url>".
				"<price>".$r['price']."</price>".
				"<currencyId>RUR</currencyId>".
				"<categoryId>".( $r['sub_r'] ? $r['sub_r'] : $r['r'] )."</categoryId>".
				( $market_category ? "<market_category>".$market_category."</market_category>" : "" ).
				( $preview ? "<picture>https://".$_SERVER['HTTP_HOST'].$mysql->settings['local_folder']."files/upload/goods/thumbs/".$preview."</picture>" : "" ).
				"<store>true</store>".
				"<pickup>true</pickup>".
				"<sales_notes>Минимальная стоимость заказа 3000 рублей!</sales_notes>".
				"<delivery>true</delivery>".
				"<local_delivery_cost>".( $r['price'] < 8000 ? 300 : 0 )."</local_delivery_cost>".
				"<typePrefix>".$type_prefix."</typePrefix>".
				"<vendor>".$lang->gp( $vendors[$r['vendor']]['value'], true )."</vendor>".
				"<vendorCode>".$r['vendor']."</vendorCode>".
				( $r['guid'] ? "<model>".trim( $r['guid'] )."</model>" : "<model>".$r['name']."</model>" ).
				( $text ? "<description>".$text."</description>" : "" ).
				"<manufacturer_warranty>true</manufacturer_warranty>".
				"<cpa>".( $r['price'] <= 0 ? 0 : 1 )."</cpa>";
				
			$lowprice = false;
			foreach( $props as $prop_id => $prop_data ) {
				$elem = $this->getElementByData( $properties, "prop_id", $prop_id );
				if( $prop_id == 2 ) {
					foreach( $properties as $p ) {
						if( $p['prop_id'] != $prop_id )
							continue;
						if( $p['value'] == 1 )
							$lowprice = true;
					}
				}
				if( $prop_id < 6 || $prop_id == 48 || $prop_id == 49 || $prop_id == 50 || $prop_id == 51 || $prop_id == 52 || !$elem || $prop_id == 53 || $prop_id == 54 || $prop_id == 55 )
					continue;
				$value = "";
				if( $prop_data['type'] == 3 ) {
					$l_elems = $main->listings->getListingElementsArraySpec( $prop_data['source'], "`order` DESC, `id` ASC", "", 0, true );
					foreach( $properties as $p ) {
						if( $p['prop_id'] != $prop_id )
							continue;
						$value .= ( $value ? ", " : "" ).$lang->gp( $l_elems[$p['value']]['value'], true );
					}
				} else if( $prop_data['type'] == 2 ) {
					$value = $main->listings->getListingElementValueById( $prop_data['source'], $elem['value'], true );
				} else if( $prop_data['type'] == 5 ) {
					foreach( $properties as $p ) {
						if( $p['prop_id'] != $prop_id )
							continue;
						$value .= ( $value ? "<br>" : "" ).$p['value'];
					}
					$value = $value ? "<div>".$value."</div>" : '';
				} else if( $prop_data['type'] == 4 ) {
					$value = $elem['value'] == 1 ? "Да" : "Нет";
				} else
					$value = $elem['value'];
				
				if( !$value )
					continue;
				
				$local .= "<param name=\"".$lang->gp( $prop_data['name'], true )."\">".strip_tags( $value )."</param>";
			}
			
			if( $lowprice ) {
				$llf = $mysql->mq( "SELECT `price` FROM ".$this->gl_dbase_string."`".$mysql->t_prefix."tovar_prices` WHERE `tovar_id`=".$r['id']." AND `prop_value`=0 ORDER BY `date` DESC" );
				if( $llf && $llf['price'] > $r['price'] && floor( $r['price'] / ( $llf['price'] / 100 ) ) < 95 )
					$local .= "<oldprice>".$llf['price']."</oldprice>";
			}
				
			$local .= "</offer>";
			
			//$t .= iconv( "UTF-8", "WINDOWS-1251", $local );
			$t .= $local;
		}
			
		$t .= "</offers></shop>";
		
		if( $query->gp( "asdfsdf" ) )
			return strlen( $t );
		
		return $t;
	}
	
	function get_xml_footer()
	{
		return '</yml_catalog>';
	}
}

?>