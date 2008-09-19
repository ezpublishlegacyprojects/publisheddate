<?php

class eZPublishedDateType extends eZDataType
{
	const DATA_TYPE_STRING = 'ezpublisheddate';
	const DEFAULT_FIELD = 'data_int1';
	const ADJUSTMENT_FIELD = 'data_text5';
	const DEFAULT_EMTPY = 0;
	const DEFAULT_CURRENT_DATE = 1;
	const DEFAULT_ADJUSTMENT = 2;
    function eZPublishedDateType()
    {
        $this->eZDataType( eZPublishedDateType::DATA_TYPE_STRING, ezi18n( 'kernel/classes/datatypes', "Set Date and Time for Content", 'Datatype name' ),
                           array( 'serialize_supported' => false ) );
    }
    function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
    {
        $obj = $contentObjectAttribute->attribute( 'object' );
        $obj->setAttribute( 'published', $contentObjectAttribute->attribute( 'data_int' ) );
        $obj->store();
    }
    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $obj = $contentObjectAttribute->attribute( 'object' );
            $dataInt = $obj->attribute( 'published' );
            $contentObjectAttribute->setAttribute( "data_int", $dataInt );
        }
        else
        {
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $defaultType = $contentClassAttribute->attribute( self::DEFAULT_FIELD );
            if ( $defaultType == self::DEFAULT_CURRENT_DATE )
            {
                $contentObjectAttribute->setAttribute( "data_int", mktime() );
            }
            else if ( $defaultType == self::DEFAULT_ADJUSTMENT )
            {
                $adjustments = eZDateTimeType::classAttributeContent( $contentClassAttribute );
                $value = new eZDateTime();
                $value->adjustDateTime( $adjustments['hour'], $adjustments['minute'], 0, $adjustments['month'], $adjustments['day'], $adjustments['year'] );
                $contentObjectAttribute->setAttribute( "data_int", $value->timeStamp() );
            }
        }
    }
    /*!
     Private method only for use inside this class
    */
    function validateDateTimeHTTPInput( $day, $month, $year, $hour, $minute, &$contentObjectAttribute )
    {
        include_once( 'lib/ezutils/classes/ezdatetimevalidator.php' );

        $state = eZDateTimeValidator::validateDate( $day, $month, $year );
        if ( $state == eZInputValidator::STATE_INVALID )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'Date is not valid.' ) );
            return eZInputValidator::STATE_INVALID;
        }

        $state = eZDateTimeValidator::validateTime( $hour, $minute );
        if ( $state == eZInputValidator::STATE_INVALID )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'Time is not valid.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return $state;
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   = $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  = $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    = $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   = $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute = $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();
            if ( $hour == '' )
                $hour = '00';
            if ( $minute == '' )
                $minute = '00';
            if ( $year == '' or
                 $month == '' or
                 $day == '' or
                 $hour == '' or
                 $minute == '' )
            {
                if ( !( $year == '' and
                        $month == '' and
                        $day == '' and
                        $hour == '' and
                        $minute == '') or
                     ( !$classAttribute->attribute( 'is_information_collector' ) and
                       $contentObjectAttribute->validateIsRequired() ) )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Missing datetime input.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                else
                    return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                return $this->validateDateTimeHTTPInput( $day, $month, $year, $hour, $minute, $contentObjectAttribute );
            }
        }
        else
            return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   = $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  = $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    = $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   = $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute = $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );
            if ( $hour == '' )
                $hour = '00';
            if ( $minute == '' )
                $minute = '00';
            $dateTime = new eZDateTime();
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            if ( ( $year == '' and $month == ''and $day == '' and
                   $hour == '' and $minute == '' ) or
                 !checkdate( $month, $day, $year ) or $year < 1970 )
            {
                    $dateTime->setTimeStamp( 0 );
            }
            else
            {
                $dateTime->setMDYHMS( $month, $day, $year, $hour, $minute, 0 );
            }

            $contentObjectAttribute->setAttribute( 'data_int', $dateTime->timeStamp() );
            return true;
        }
        return false;
    }

    /*!
     \reimp
    */
    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   = $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  = $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    = $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   = $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute = $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();
            if ( $hour == '' )
                $hour = '00';
            if ( $minute == '' )
                $minute = '00';
            if ( $year == '' or
                 $month == '' or
                 $day == '' or
                 $hour == '' or
                 $minute == '' )
            {
                if ( !( $year == '' and
                        $month == '' and
                        $day == '' and
                        $hour == '' and
                        $minute == '') or
                     $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Missing datetime input.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                else
                    return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                return $this->validateDateTimeHTTPInput( $day, $month, $year, $hour, $minute, $contentObjectAttribute );
            }
        }
        else
            return eZInputValidator::STATE_INVALID;
    }

   /*!
    \reimp
    Fetches the http post variables for collected information
   */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $year   = $http->postVariable( $base . '_datetime_year_' . $contentObjectAttribute->attribute( 'id' ) );
            $month  = $http->postVariable( $base . '_datetime_month_' . $contentObjectAttribute->attribute( 'id' ) );
            $day    = $http->postVariable( $base . '_datetime_day_' . $contentObjectAttribute->attribute( 'id' ) );
            $hour   = $http->postVariable( $base . '_datetime_hour_' . $contentObjectAttribute->attribute( 'id' ) );
            $minute = $http->postVariable( $base . '_datetime_minute_' . $contentObjectAttribute->attribute( 'id' ) );

            $dateTime = new eZDateTime();
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            if ( ( $year == '' and $month == ''and $day == '' and
                   $hour == '' and $minute == '' ) or
                 !checkdate( $month, $day, $year ) or $year < 1970 )
            {
                    $dateTime->setTimeStamp( 0 );
            }
            else
            {
                $dateTime->setMDYHMS( $month, $day, $year, $hour, $minute, 0 );
            }

            $collectionAttribute->setAttribute( 'data_int', $dateTime->timeStamp() );
            return true;
        }
        return false;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $dateTime = new eZDateTime();
        $stamp = $contentObjectAttribute->attribute( 'data_int' );
        $dateTime->setTimeStamp( $stamp );
        return $dateTime;
        $dateTime = new eZDateTime();
        $obj = $contentObjectAttribute->attribute( 'object' );
        $stamp = $obj->attribute( 'published' );
        $dateTime->setTimeStamp( $stamp );
        return $dateTime;
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     \reimp
    */
    function isInformationCollector()
    {
        return true;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }
    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_int' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( 'data_int', $string );
    }

    /*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( $classAttribute )
    {
        if ( $classAttribute->attribute( self::DEFAULT_FIELD ) == null )
            $classAttribute->setAttribute( self::DEFAULT_FIELD, 0 );
        $classAttribute->store();
    }

    function &parseXML( $xmlText )
    {
        include_once( 'lib/ezxml/classes/ezxml.php' );
        $xml = new eZXML();
        $dom =& $xml->domTree( $xmlText );
        return $dom;
    }

    function classAttributeContent( $classAttribute )
    {
        $xmlText = $classAttribute->attribute( 'data_text5' );
        if ( trim( $xmlText ) == '' )
        {  
            $classAttrContent = eZPublishedDateType::defaultClassAttributeContent();
            return $classAttrContent;
        }  
        $doc = eZPublishedDateType::parseXML( $xmlText );
        $root = $doc->root();
        $type = $root->elementByName( 'year' );
        if ( $type )
        {
            $content['year'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'month' );
        if ( $type )
        {
            $content['month'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'day' );
        if ( $type )
        {
            $content['day'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'hour' );
        if ( $type )
        {
            $content['hour'] = $type->attributeValue( 'value' );
        }
        $type = $root->elementByName( 'minute' );
        if ( $type )
        {
            $content['minute'] = $type->attributeValue( 'value' );
        }
        return $content;
    }

    function defaultClassAttributeContent()
    {
        return array( 'year' => '',
                      'month' => '',
                      'day' => '',
                      'hour' => '',
                      'minute' => '' );
    }


    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $default = $base . "_ezpublisheddate_default_" . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $default ) )
        {
            $defaultValue = $http->postVariable( $default );
            $classAttribute->setAttribute( self::DEFAULT_FIELD,  $defaultValue );
            if ( $defaultValue == self::DEFAULT_ADJUSTMENT )
            {
                $doc = new eZDOMDocument( 'DateTimeAdjustments' );
                $root = $doc->createElementNode( 'adjustment' );
                $contentList = eZPublishedDateType::contentObjectArrayXMLMap();
                foreach ( $contentList as $key => $value )
                {
                    $postValue = $http->postVariable( $base . '_ezpublisheddate_' . $value . '_' . $classAttribute->attribute( 'id' ) );
                    unset( $elementType );
                    $elementType = $doc->createElementNode( $key, array( 'value' => $postValue ) );
                    $root->appendChild( $elementType );
                }
                $doc->setRoot( $root );
                $docText = $doc->toString();
                $classAttribute->setAttribute( self::ADJUSTMENT_FIELD , $docText );
            }
        }
        return true;
    }

    function contentObjectArrayXMLMap()
    {
        return array( 'year' => 'year',
                      'month' => 'month',
                      'day' => 'day',
                      'hour' => 'hour',
                      'minute' => 'minute' );
    }


    /*!
     Returns the date.
    */
    function title( $contentObjectAttribute, $name = null )
    {
        $locale = eZLocale::instance();
        $retVal = $contentObjectAttribute->attribute( "data_int" ) == 0 ? '' : $locale->formatDateTime( $contentObjectAttribute->attribute( "data_int" ) );
        return $retVal;
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return true;
    }

    /*!
     \reimp
    */
    function sortKey( $contentObjectAttribute )
    {
        return (int)$contentObjectAttribute->attribute( 'data_int' );
    }

        /*!
     \reimp
    */
    function sortKeyType()
    {
        return 'int';
    }


    /*!
     \reimp
    */
    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $defaultValue = $classAttribute->attribute( self::DEFAULT_FIELD );
        $defaultValueNode = $attributeParametersNode->ownerDocument->createElement( 'default-value' );

        switch ( $defaultValue )
        {
            case self::DEFAULT_CURRENT_DATE:
            {
                $defaultValueNode->setAttribute( 'type', 'current-date' );
            } break;
            case self::DEFAULT_ADJUSTMENT:
            {
                $defaultValueNode->setAttribute( 'type', 'adjustment' );

                $adjustDOMValue = new DOMDocument( '1.0', 'utf-8' );
                $adjustValue = $classAttribute->attribute( self::ADJUSTMENT_FIELD );
                $success = $adjustDOMValue->loadXML( $adjustValue );

                if ( $success )
                {
                    $adjustmentNode = $adjustDOMValue->getElementsByTagName( 'adjustment' )->item( 0 );

                    if ( $adjustmentNode )
                    {
                        $importedAdjustmentNode = $defaultValueNode->ownerDocument->importNode( $adjustmentNode, true );
                        $defaultValueNode->appendChild( $importedAdjustmentNode );
                    }
                }
            } break;
            case self::DEFAULT_EMTPY:
            {
                $defaultValueNode->setAttribute( 'type', 'empty' );
            } break;
            default:
            {
                eZDebug::writeError( 'Unknown type of DateTime default value. Empty type used instead.',
                                    'eZDateTimeType::serializeContentClassAttribute()' );
                $defaultValueNode->setAttribute( 'type', 'empty' );
            } break;
        }

        $attributeParametersNode->appendChild( $defaultValueNode );
    }

    /*!
     \reimp
    */
    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $defaultValue = '';
        $defaultNode = $attributeParametersNode->getElementsByTagName( 'default-value' )->item( 0 );
        if ( $defaultNode )
        {
            $defaultValue = strtolower( $defaultNode->getAttribute( 'type' ) );
        }
        switch ( $defaultValue )
        {
            case 'current-date':
            {
                $classAttribute->setAttribute( self::DEFAULT_FIELD, self::DEFAULT_CURRENT_DATE );
            } break;
            case 'adjustment':
            {
                $adjustmentValue = '';
                $adjustmentNode = $defaultNode->getElementsByTagName( 'adjustment' )->item( 0 );
                if ( $adjustmentNode )
                {
                    $adjustmentDOMValue = new DOMDocument( '1.0', 'utf-8' );
                    $importedAdjustmentNode = $adjustmentDOMValue->importNode( $adjustmentNode, true );
                    $adjustmentDOMValue->appendChild( $importedAdjustmentNode );
                    $adjustmentValue = $adjustmentDOMValue->saveXML();
                }

                $classAttribute->setAttribute( self::DEFAULT_FIELD, self::DEFAULT_ADJUSTMENT );
                $classAttribute->setAttribute( self::ADJUSTMENT_FIELD, $adjustmentValue );
            } break;
            case 'empty':
            {
                $classAttribute->setAttribute( self::DEFAULT_FIELD, self::DEFAULT_EMTPY );
            } break;
            default:
            {
                eZDebug::writeError( 'Type of DateTime default value is not set. Empty type used as default.',
                                    'eZDateTimeType::unserializeContentClassAttribute()' );
                $classAttribute->setAttribute( self::DEFAULT_FIELD, self::DEFAULT_EMTPY );
            } break;
        }
    }
}

eZDataType::register( eZPublishedDateType::DATA_TYPE_STRING, "ezpublisheddatetype" );

?>
