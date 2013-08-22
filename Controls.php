<?php
/************************************************************
 *
 *    There should be no real reason to change this.
 *    But please contact Edgy if you feel there is 
 *    something to be added or tweaked.  
 *
 **********************************************************/    

Class Controls{

	public function input_display($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    $class = isset($opts['class'])?"class=\"{$opts['class']}\"":false;
    $style = isset($opts['style'])?"style=\"{$opts['style']}\"":false;
    $value = isset($opts['value'])?trim(htmlentities($opts['value'])):false;
    $model = isset($opts['model'])?$opts['model']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    $date = isset($opts['date'])?$opts['date']:false;

    if($model) {
      $value = trim(htmlentities(@$model[1][$name]));
      $name = $model[0].'_'.$name;
    }
    if($date) {
      $value = display_date($value);
    }
    $input = "$label <span id=\"$id\" $class $style>$value</span>";
    return $input;
	}
	
	public function input_text($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    if(detect_ie()) {
      $class = isset($opts['class'])?"class=\"text {$opts['class']}\"":"class=\"text\"";
    } else {
      $class = isset($opts['class'])?"class=\"{$opts['class']}\"":false;
    }
    $readonly = isset($opts['readonly'])?$opts['readonly']:false;
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style=\"{$opts['style']}\"":false;
    $value = isset($opts['value'])?"value=\"".trim(htmlentities($opts['value']))."\"":false;
    $size = isset($opts['size'])?"size=\"{$opts['size']}\"":false;
    $model = isset($opts['model'])?$opts['model']:false;
    $maxlength = isset($opts['maxlength'])?"maxlength = \"{$opts['maxlength']}\"":false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    if($model) {
      $value = "value = \"".trim(htmlentities(@$model[1][$name]))."\"";
      $name = $model[0].'_'.$name;
    }
    $input = "$label <input type=\"text\" name=\"$name\" id=\"$id\" $class $value $size $maxlength $readonly $disabled $style />";
    return $input; 
	}
	
	public function input_textarea($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    if(detect_ie()) {
      $class = isset($opts['class'])?"class=\"text {$opts['class']}\"":"class=\"text\"";
    } else {
      $class = isset($opts['class'])?"class=\"{$opts['class']}\"":false;
    }
    $readonly = isset($opts['readonly'])?$opts['readonly']:false;
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style=\"{$opts['style']}\"":false;
    $value = isset($opts['value'])?trim(htmlentities($opts['value'])):false;
    $cols = isset($opts['dimensions'])?"cols =\"{$opts['dimensions'][0]}\"":false;
    $rows = isset($opts['dimensions'])?"rows =\"{$opts['dimensions'][1]}\"":false;
    $model = isset($opts['model'])?$opts['model']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;

    if($model) {
      $value = trim(htmlentities(@$model[1][$name]));
      $name = $model[0].'_'.$name;
    }
    $input = "$label <textarea name=\"$name\" id=\"$id\" $class $rows $cols $readonly $disabled $style>$value</textarea>";
    return $input;
	}
	
	public function input_date($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    if(detect_ie()) {
      $class = isset($opts['class'])?"class=\"text date {$opts['class']}\"":"class=\"text date\"";
    } else {
      $class = isset($opts['class'])?"class=\"date {$opts['class']}\"":"class=\"date\"";
    }
    $readonly = "readonly";
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style=\"{$opts['style']}\"":false;
    $value = isset($opts['value'])?"value=\"".trim(htmlentities(display_date($opts['value'])))."\"":false;
    $model = isset($opts['model'])?$opts['model']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    if($model) {
      $value = "value = \"".trim(htmlentities(display_date(@$model[1][$name])))."\"";
      $name = $model[0].'_'.$name;
    }
    
    $input = "$label <input type=\"text\" name=\"$name\" id=\"$id\" $class $value $readonly $disabled $style />";
    
    return $input; 
	}
	
	public function input_datetime($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    if(detect_ie()) {
      $class = isset($opts['class'])?"class=\"text datetime {$opts['class']}\"":"class=\"text datetime\"";
    } else {
      $class = isset($opts['class'])?"class=\"datetime {$opts['class']}\"":"class=\"datetime\"";
    }        
    $readonly = "readonly";
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style=\"{$opts['style']}\"":false;
    $value = isset($opts['value'])?"value=\"".trim(htmlentities($opts['value']))."\"":false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    $input = "$label <input type=\"text\" name=\"$name\" id=\"$id\" $class $value $readonly $disabled $style />";
    
    return $input; 
	}	
	
	public function input_select($name, $array, $value, $text, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    $class = isset($opts['class'])?"class = \"{$opts['class']}\"":false;
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style = \"{$opts['style']}\"":false;
    $selection = isset($opts['selection'])?$opts['selection']:false;
    $default = isset($opts['default'])?$opts['default']:false;
    $size = isset($opts['size'])?"size = ".$opts['size']:false;
    $multiple = is_array($selection)?"multiple":false;
    $model = isset($opts['model'])?$opts['model']:false;
    $object = isset($opts['object'])?$opts['object']:false;
    $date = isset($opts['date'])?$opts['date']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    if($model) {
      $selection = @$model[1][$name];
      $name = $model[0].'_'.$name;
    }

    $input = "$label\n<select name=\"$name\" id=\"$id\" $class $disabled $size $multiple>\n";
    
    if($default) {
      if(is_array($default)) {
        $input .= "\t<option value=\"{$default[0]}\">{$default[1]}</option>\n";    
      } else {
        $input .= "\t<option value=\"\">$default</option>\n";  
      }
    }
    
    if(!$object) {
      foreach ($array as $row) {
        if (is_array($selection)) {
          $selected = "";
          foreach($selection as $select) {
            if ($row[$value] == $select) {
              $selected = "selected";
            }
          }
        } elseif ($row[$value] == $selection) {
          $selected = "selected";
        } else {
          $selected = "";
        }    
        $display = $date?display_date($row[$text]):$row[$text];
        $input .= "\t<option value=\"{$row[$value]}\" $selected>$display</option>\n";
      }
    } else {
      foreach ($array as $row) {
        if (is_array($selection)) {
          $selected = "";
          foreach($selection as $select) {
            if ($row->$value == $select) {
              $selected = "selected";
            }
          }
        } elseif ($row->$value == $selection) {
          $selected = "selected";
        } else {
          $selected = "";
        }    
        
        $input .= "\t<option value=\"{$row->$value}\" $selected>{$row->$text}</option>\n";
      }    
    }
    $input .= "</select>";
    
    return $input; 
	}	
	
	public function input_hidden($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    $value = isset($opts['value'])?"value = \"".trim(htmlentities($opts['value']))."\"":false;
    $model = isset($opts['model'])?$opts['model']:false;
    
    if($model) {
      $value = "value = \"".trim(htmlentities(@$model[1][$name]))."\"";
      $name = $model[0].'_'.$name;
    }
    
    $input = "<input type=\"hidden\" name=\"$name\" id=\"$id\" $value />";
    
    return $input; 
	}	
	
	public function input_submit($name, $opts=array()){
    $id = isset($opts['id'])?$opts['id']:$name;
    $value = isset($opts['value'])?"value = \"".trim(htmlentities($opts['value']))."\"":false;

    $input = "<input type=\"submit\" name=\"$name\" id=\"$id\" $value />";
    
    return $input; 
	}			
	
	public function create_table($rows, $head = array(), $opts = array()){
    $tableClass = isset($opts['tableClass'])?" class=\"".$opts['tableClass']."\"":false; 
	  $theadClass = isset($opts['theadClass'])?" class=\"".$opts['theadClass']."\"":false; 
	  $thClass = isset($opts['thClass'])?" class=\"".$opts['thClass']."\"":false;  
	  $trClass = isset($opts['trClass'])?" class=\"".$opts['trClass']."\"":false; 
	  $tdClass = isset($opts['tdClass'])?$opts['tdClass']:false;
	  $style = isset($opts['style'])?" style=\"".$opts['style']."\"":false;
	  $cellspacing = isset($opts['cellspacing'])?"cellspacing=\"".$opts['cellspacing']."\"":false;
	  $cellpadding = isset($opts['cellpadding'])?"cellpadding=\"".$opts['cellpadding']."\"":false;
	  $id = isset($opts['id'])?" id=\"".$opts['id']."\"":false; 
    $flip = is_array($tdClass);
  
    $table = "\n<table$id $tableClass $style $cellspacing $cellpadding>";
    if(!empty($head)){
      $table .= "\n\t<thead$theadClass>";
      $table .= "\n\t\t<th$thClass>" . implode("</th>\n\t\t<th$thClass>", $head) . "</th>";
      $table .= "\n\t</thead>";
    }
    
    $flipper = 0;
    foreach($rows as $row){
       if($flip){
        $class = " class=\"".$tdClass[$flipper]."\"";
       } else {
        $class = " class=\"".$tdClass."\"";
       }
    
      $table .= "\n\t<tr$trClass>";
      $table .= "\n\t\t<td$class>" . implode("</td>\n\t\t<td$class>", $row) . "</td>";
      $table .= "\n\t</tr>";
      
      $flipper = $flipper == 0? 1:0;
    }
    
    $table .= "\n</table>\n";
    
    return $table;
  }
  
  public function input_radio($name, $array, $value, $text, $opts=array()) {
    $id = isset($opts['id'])?$opts['id']:$name;
    $class = isset($opts['class'])?"class = \"{$opts['class']}\"":false;
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style = \"{$opts['style']}\"":false;
    $selection = isset($opts['selection'])?$opts['selection']:false;
    $seperator = isset($opts['seperator'])?$opts['seperator']:"";
    $model = isset($opts['model'])?$opts['model']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    if($model) {
      $selection = @$model[1][$name];
      $name = $model[0].'_'.$name;
    }    
    
    $input = "";
    foreach ($array as $row) {
      if ($row[$value] == $selection) {
        $selected = "checked";
      } else {
        $selected = "";
      }
      $input .= "$label \t<input type=\"radio\" name=\"$name\" value=\"{$row[$value]}\" $selected $disabled $class $style />{$row[$text]}$seperator\n";
    }
    
    return $input;     
  }
  
  public function input_check($name, $array, $value, $text, $opts=array()) {
    $id = isset($opts['id'])?$opts['id']:$name;
    $class = isset($opts['class'])?"class = \"{$opts['class']}\"":false;
    $disabled = isset($opts['disabled'])?$opts['disabled']:false;
    $style = isset($opts['style'])?"style = \"{$opts['style']}\"":false;
    $selection = isset($opts['selection'])?$opts['selection']:false;
    $seperator = isset($opts['seperator'])?$opts['seperator']:"";
    $model = isset($opts['model'])?$opts['model']:false;
    $label = isset($opts['label'])?"<label for=\"$id\">{$opts['label']}</label>":false;
    
    if($model) {
      $selection = @$model[1][$name];
      $name = $model[0].'_'.$name;
    }   
        
    $input = "";
    foreach ($array as $row) {
      if (is_array($selection)) {
        $selected = "";
        foreach($selection as $select) {
          if (@$row[$value] == $select) {
            $selected = "checked";
          }
        }
      } elseif (@$row[$value] == $selection) {
        $selected = "checked";
      } else {
        $selected = "";
      }
      $input .= "$label \t<input type=\"checkbox\" name=\"$name\" value=\"".@$row[$value]."\" $selected $disabled $class $style />".@$row[$text]."$seperator\n";
    }
    
    return $input;    
  }
  
  public function form($page, $controller, $method, $vars=array(), $opts=array()) {
    $enctype = isset($opts['enctype'])?"enctype = \"{$opts['enctype']}\"":false;
    switch($method) {
      case 'POST':
        $form = "\n<form action=\"".link_to($page, $controller, $vars)."\" method=\"$method\" $enctype>\n";
        break;
      case 'GET':
        $form  = "\n<form method=\"$method\" $enctype>";
        $form .= "\n\t".self::input_hidden('c',array('value'=>$controller));
        $form .= "\n\t".self::input_hidden('p',array('value'=>$page));
        foreach($vars as $k=>$v) {
          $form .= "\n\t".self::input_hidden($k,array('value'=>$v));
        }
        $form .= "\n";
    }
    return $form;
  }
  
  public function endform(){
    return "</form>\n";
  }
}
?>