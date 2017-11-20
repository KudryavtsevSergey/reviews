<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->addExternalJS($templateFolder . "/jquery.raty.js");
$this->setFrameMode(false);

if (!empty($arResult["ERRORS"])):?>
    <? ShowError(implode("<br />", $arResult["ERRORS"])) ?>
<?endif;
if (strlen($arResult["MESSAGE"]) > 0):?>
    <? ShowNote($arResult["MESSAGE"]) ?>
<? endif ?>
<h2>ДОБАВИТЬ ОТЗЫВ</h2>
<form class="form-horizontal row" method="post">
    <fieldset>
        <?= bitrix_sessid_post() ?>
        <? if ($arParams["MAX_FILE_SIZE"] > 0): ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $arParams["MAX_FILE_SIZE"] ?>"/>
        <? endif ?>
        <? if (is_array($arResult["PROPERTY_LIST"]) && !empty($arResult["PROPERTY_LIST"])): ?>
            <? foreach ($arResult["PROPERTY_LIST"] as $propertyID): ?>
                <div class="form-group col-sm-12">
                    <label>
                        <? if (intval($propertyID) > 0): ?>
                            <?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"] ?>
                        <? else: ?>
                            <?= !empty($arParams["CUSTOM_TITLE_" . $propertyID]) ? $arParams["CUSTOM_TITLE_" . $propertyID] : GetMessage("IBLOCK_FIELD_" . $propertyID) ?>
                        <? endif ?>
                        <? if (in_array($propertyID, $arResult["PROPERTY_REQUIRED"])): ?>
                            <span class="starrequired">*</span>
                        <? endif ?>
                    </label>
                    <?
                    if (intval($propertyID) > 0) {
                        if (
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "T"
                            &&
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] == "1"
                        )
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "S";
                        elseif (
                            (
                                $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "S"
                                ||
                                $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "N"
                            )
                            &&
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] > "1"
                        )
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "T";
                    } elseif (($propertyID == "TAGS") && CModule::IncludeModule('search'))
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "TAGS";

                    if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y") {
                        $inputNum = ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) ? count($arResult["ELEMENT_PROPERTIES"][$propertyID]) : 0;
                        $inputNum += $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE_CNT"];
                    } else {
                        $inputNum = 1;
                    }

                    if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"])
                        $INPUT_TYPE = "USER_TYPE";
                    else
                        $INPUT_TYPE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"];

                    switch ($INPUT_TYPE):
                    case "USER_TYPE":
                    for ($i = 0;
                         $i < $inputNum;
                         $i++) {
                        if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                            $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                        } elseif ($i == 0) {
                            $value = intval($propertyID) > 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                        } else {
                            $value = "";
                        }
                        ?>
                        <textarea cols="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                                  rows="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] ?>"
                                  class="form-control"
                                  name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]"><?= $value ?></textarea>
                    <?
                    }
                    break;
                    case "S":
                    case "N":
                    for ($i = 0;
                    $i < $inputNum;
                    $i++) {
                    if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                        $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                    } elseif ($i == 0) {
                        $value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];

                    } else {
                        $value = "";
                    }
                    ?>
                    <input class="form-control" type="text" name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]"
                           size="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"]; ?>"
                           value="<?= $value ?>"/>
                    <?
                    }
                    break;
                    case "F":
                    for ($i = 0;$i < $inputNum;$i++) {
                        $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                        ?>
                        <input class="form-control" type="hidden"
                               name="PROPERTY[<?= $propertyID ?>][<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>]"
                               value="<?= $value ?>"/>
                        <input class="form-control" type="file"
                               size="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                               name="PROPERTY_FILE_<?= $propertyID ?>_<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>"/>
                        <?
                    }
                    break;
                    case "L":
                    if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["LIST_TYPE"] == "C")
                        $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "checkbox" : "radio";
                    else
                        $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";

                    ?>
                        <?/*<select name="PROPERTY[<?=$propertyID?>]<?=$type=="multiselect" ? "[]\" size=\"".$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"]."\" multiple=\"multiple" : ""?>">
                            <option value=""><?echo GetMessage("CT_BIEAF_PROPERTY_VALUE_NA")?></option>
                            <?
                            if (intval($propertyID) > 0) $sKey = "ELEMENT_PROPERTIES";
                            else $sKey = "ELEMENT";

                            foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum)
                            {
                                $checked = false;
                                if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
                                {
                                    foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum)
                                    {
                                        if ($key == $arElEnum["VALUE"])
                                        {
                                            $checked = true;
                                            break;
                                        }
                                    }
                                }
                                else
                                {
                                    if ($arEnum["DEF"] == "Y") $checked = true;
                                }
                                ?>
                                <option value="<?=$key?>" <?=$checked ? " selected=\"selected\"" : ""?>><?=$arEnum["VALUE"]?></option>
                                <?
                            }
                            ?>
                        </select>*/?>
                        <div class="rating-wrapper">
                            <div class="raty-wrapper">
                                <div class="star-rating" data-rating-score="0"></div>
                            </div>
                        </div>

                        <script type="text/javascript">
                            // Default size star
                            $.fn.raty.defaults.path = '<?=$templateFolder?>/images';
                            $('.star-rating').raty({
                                space: false,
                                scoreName: "PROPERTY[<?= $propertyID ?>]<?= $type == "multiselect" ? "[]\" size=\"" . $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] . "\" multiple=\"multiple" : "" ?>",
                                score: function () {
                                    return $(this).attr('data-rating-score');
                                }
                            });
                        </script>
                        <?
                    endswitch; ?>
                </div>
            <? endforeach; ?>
            <? /* if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0): ?>
                <tr>
                    <td><?= GetMessage("IBLOCK_FORM_CAPTCHA_TITLE") ?></td>
                    <td>
                        <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                             width="180" height="40" alt="CAPTCHA"/>
                    </td>
                </tr>
                <tr>
                    <td><?= GetMessage("IBLOCK_FORM_CAPTCHA_PROMPT") ?><span class="starrequired">*</span>:</td>
                    <td><input type="text" name="captcha_word" maxlength="50" value=""></td>
                </tr>
            <? endif */ ?>
        <? endif ?>
        <div class="form-group col-sm-12">
            <input type="submit" class="btn btn-primary btnus" name="iblock_submit"
                   value="<?= GetMessage("IBLOCK_FORM_SUBMIT") ?>"/>
        </div>
    </fieldset>
</form>