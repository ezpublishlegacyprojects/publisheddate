{* DO NOT EDIT THIS FILE! Use an override template instead. *}
<div class="block">
    <label>{'Default value'|i18n( 'design/standard/class/datatype' )}:</label>
    <p>
    {switch match=$class_attribute.data_int1}
    {case match=1}
        {'Current datetime'|i18n( 'design/standard/class/datatype' )}
    {/case}

    {case match=2}
        {'Current datetime'|i18n( 'design/standard/class/datatype' )}

        {section show=$class_attribute.content.year}
        {section show=$class_attribute.content.year|gt(0)}+{/section}{$class_attribute.content.year} {'year(s)'|i18n( 'design/standard/class/datatype' )}
        {/section}

        {section show=$class_attribute.content.month}
        {section show=$class_attribute.content.month|gt(0)}+{/section}{$class_attribute.content.month} {'month(s)'|i18n( 'design/standard/class/datatype' )}
        {/section}

        {section show=$class_attribute.content.day}
        {section show=$class_attribute.content.day|gt(0)}+{/section}{$class_attribute.content.day} {'day(s)'|i18n( 'design/standard/class/datatype' )}
        {/section}

        {section show=$class_attribute.content.hour}
        {section show=$class_attribute.content.hour|gt(0)}+{/section}{$class_attribute.content.hour} {'hour(s)'|i18n( 'design/standard/class/datatype' )}
        {/section}

        {section show=$class_attribute.content.minute}
        {section show=$class_attribute.content.minute|gt(0)}+{/section}{$class_attribute.content.minute} {'minute(s)'|i18n( 'design/standard/class/datatype' )}
        {/section}
    {/case}

    {case}
        <i>{'Empty'|i18n( 'design/standard/class/datatype' )}</i>
    {/case}
    {/switch}
    </p>
</div>
