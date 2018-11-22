<?php

//var_dump($_REQUEST);


// START functions for question types

// function : html($question)
// input : $question = question object

function html($question)
{
    return $question->html;
}

// function : text($question)
// input : $question = question object

function text($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $existingValueAttribute = '';
    if(isset($savedData->$id)) $existingValueAttribute = ' value="' . $savedData->$id . '"';
    
    $html = $question->question . ' <input type="text" name="' . $id . '"' . $existingValueAttribute . '><br>';
    return $html;
}

function textarea($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $existingValueAttribute = '';
    if(isset($savedData->$id)) $existingValueAttribute = $savedData->$id;
    
    $html = $question->question  . '<textarea name="' . $id . '" form="survey">' . $existingValueAttribute . '</textarea><br>';
    return $html;
}

function checkbox($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $html = $question->question . '<br>';
    foreach($question->values as $value)
    {
        $existingValueAttribute = '';
        $id_value = $id . '_' . $value; // workaround so tha we can have more than one unique value checked
        if(isset($savedData->$id_value ) && $savedData->$id_value == $value) $existingValueAttribute = " checked";
        
        //Since we want to send something also if user unchecked the box we have a hidden field that gives a value if the box isn't checked. (PHP uses the last value so if the hidden field is before the visible checkbox thsi should work)
        $html .= '<input type="hidden" name="' . $id . '_' . $value . '" value="null">';
        $html .= '<input type="checkbox" name="' . $id . '_' . $value . '" value="' . $value . '"' . $existingValueAttribute . '>' . $value . '<br>';
    }
    
    return $html;
}

function radiobutton($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $html = $question->question . '<br>';
    foreach($question->values as $value)
    {
        $existingValueAttribute = '';
        if(isset($savedData->$id) && $savedData->$id == $value) $existingValueAttribute = " checked";
        
        $html .= '<input type="radio" name="' . $id . '" value="' . $value . '"' . $existingValueAttribute . '>' . $value . '<br>';
    }
    
    return $html;
}

function likert($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $html = $question->question . '<br>';
    $html .= $question->start;
    for ( $i = 1 ; $i < 6 ; $i++)
    {
        $existingValueAttribute = '';
        if(isset($savedData->$id) && $savedData->$id == $i) $existingValueAttribute = " checked";
        
        $html .= '<label> ' . $i . '<br /><input class="likert" type="radio" name="' . $id . '" value="' . $i . '"' . $existingValueAttribute . '></label>';
    }
    $html .= $question->end . '<br>';
   
    return $html;
}

function select($question)
{
    global $savedData;  //so that we can see if this already had an value.
    $id = $question->id;
    
    $html = $question->question . '<select name="' . $id . '" form="survey">';
    foreach($question->values as $value)
    {
        $existingValueAttribute = '';
        if(isset($savedData->$id) && $savedData->$id == $value) $existingValueAttribute = " selected";
        
        $html .= '<option value="' . $value . '"' . $existingValueAttribute . '>' . $value . '</option>';
    }
    $html .= '.</select><br>';
  
    return $html;
}

function nextPage($question)
{
    $html = ''; 
    if($GLOBALS['page'] != 1) $html .= '<button type="submit" name="page" value="' . ($GLOBALS['page']-1) . '">' . $question->previousValue . '</button>';
    $html .= '<button type="submit" name="page" value="' . ($GLOBALS['page']+1) . '">' . $question->nextValue . '</button><br>';
    return $html;
}

function submit($question)
{
    $html = ''; 
    if($GLOBALS['page'] != 1) $html .= '<button type="submit" name="page" value="' . ($GLOBALS['page']-1) . '">' . $question->previousValue . '</button>'; // Jotta meill채 on "edellinen" -painike
    $html .= '<button type="submit" name="save" value="1">' . $question->submitValue . '</ button>';
    return $html;
}

// END functions for question types

extract($_REQUEST);

if(!isset($page)) $page = 1;
if(!isset($u)) $u = dechex(rand(0,268435455));    // up to fffffff ($u = user id)

// getting this for two reasons.
// 1. next we're going to save here any new data we were just sent
// 2. we'll use this topopulate fields with already existing answers
if(file_exists("./answers/" . $u . ".json"))
{   // if the file already exists, we'll get that info
    $savedData = json_decode(file_get_contents("./answers/" . $u . ".json"));
}
else{   // to prevent "PHP Warning:  Creating default object from empty value in ..." later on 
    $savedData = new StdClass;
}

// saving data we got from previous page
if(isset($forminfo) & $forminfo == "active")
{   // forminfo should have value "active" since we set it so in the form in a hidden field. If we don't have it, we know we shouldn't have any other data either so we don't have to save anything. We probably could also use some other field or do something else instead.
    
    foreach($_REQUEST as $key => $value)
    {   // save all data to the object
        $savedData->$key = $value;
    }
    
    file_put_contents("./answers/" . $u . ".json", json_encode($savedData,JSON_PRETTY_PRINT));
}



if(!file_exists("./questionnaire.json"))
{
    echo "json file missing";
    die();
}

$form = json_decode(file_get_contents("./questionnaire.json"));
$html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Ubuntu" />
<style>
* {
    font-family: Ubuntu;
    box-sizing: border-box;
}

body {
    margin: 0;
    background-image: url("./logo.png");
    background-repeat: no-repeat;
    background-position: 0px 20px;
}

.column {
    float: left;
    padding: 10px;
}

.left, .right {
  width: 25%;
}

.middle {
  width: 50%;
  min-width: 500px;
  opacity: 0.7;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

/* yksi vaihtoehto täällä https://stackoverflow.com/questions/22458889/how-can-i-align-a-radio-buttons-text-under-the-button-itself*/
/* tai vain ilman inputtiin koskemista
label {
  float: left;
  padding: 0 1em;
  text-align: center;
}
*/
label {
    display: inline-block;
    text-align: center;
}


@media screen and (max-width: 1000px) { 

    body {
        font-size: 120%;
    }
    
    .left, .right {
      width: 0%;
    }
    
    .middle {
      width: 100%;
      opacity: 0.7;
    }
}
</style>


</head><body><div class="row"><div class="column left" style="background-color:#fff;"></div><div class="column middle" style="background-color:#fff;"><form action="./index.php" id="survey" method="post"> <input type="hidden" name="forminfo" value="active"> <input type="hidden" name="u" value="' . $u . '">';


//var_dump($form->questions);
//$questions = $form->questions;
//var_dump($questions);
foreach($form->questions as $question)
{
    if($question->page == $page)
    {
        $questionType = $question->type;
        $html .= $questionType($question);
        $html .= "<br><br>";
    }
}

    $html .= '</div><div class="column right" style="background-color:#fff;"></div></div>  </body></html>';
echo $html;


//var_dump($form->questions[0]);
//property_exists

?>