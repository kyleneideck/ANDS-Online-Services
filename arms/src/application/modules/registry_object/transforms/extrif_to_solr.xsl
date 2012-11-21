<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output indent="yes" />
    <xsl:strip-space elements="*"/>

<xsl:template match="/">
    <xsl:apply-templates/>
</xsl:template>   
    
 <xsl:template match="registryObjects">
    <xsl:apply-templates select="registryObject"/>
 </xsl:template> 

    <xsl:template match="registryObject">
        <add>
        <doc>
        <xsl:variable name="roKey" select="key"/>
            <xsl:apply-templates select="key"/>

        <xsl:choose>
			<xsl:when test="extRif:extendedMetadata">
	        	<xsl:apply-templates select="extRif:extendedMetadata/extRif:id"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceID"/>
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:status"/>
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:reverseLinks"/> 
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:searchBaseScore"/>
	 			<xsl:apply-templates select="extRif:extendedMetadata/extRif:registryDateModified"/>
	            <xsl:apply-templates select="originatingSource"/>
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceKey"/> 
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceKeyHash"/> 
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/> 
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:listTitle"/>  
	            <xsl:apply-templates select="extRif:extendedMetadata/extRif:flag"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:warning_count"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:error_count"/>
     			<xsl:apply-templates select="extRif:extendedMetadata/extRif:url_slug"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:manually_assessed_flag"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:gold_status_flag"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:quality_level"/>
      			<xsl:apply-templates select="extRif:extendedMetadata/extRif:feedType"/>   
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:lastModifiedBy"/>  
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:spatialGeometry/extRif:geometry"/>             	
        	</xsl:when>
        </xsl:choose>

            <xsl:element name="field">
                <xsl:attribute name="name">group</xsl:attribute>
                <xsl:value-of select="@group"/>
            </xsl:element>  
            <xsl:apply-templates select="collection | party | activity | service"/>

        </doc>
        </add>
    </xsl:template> 
   
    <xsl:template match="key">
        <xsl:element name="field">
            <xsl:attribute name="name">key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:urlSlug">
        <xsl:element name="field">
            <xsl:attribute name="name">url_slug</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
	
	
    <xsl:template match="extRif:id">
        <xsl:element name="field">
            <xsl:attribute name="name">id</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:dataSourceID">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_id</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    

    <xsl:template match="extRif:flag">
        <xsl:element name="field">
            <xsl:attribute name="name">flag</xsl:attribute>
            <xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:warning_count">
        <xsl:element name="field">
            <xsl:attribute name="name">warning_count</xsl:attribute>
            <xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:error_count">
        <xsl:element name="field">
            <xsl:attribute name="name">error_count</xsl:attribute>
            <xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:url_slug">
        <xsl:element name="field">
            <xsl:attribute name="name">url_slug</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:manually_assessed_flag">
        <xsl:element name="field">
            <xsl:attribute name="name">manually_assessed_flag</xsl:attribute>
			<xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:gold_status_flag">
        <xsl:element name="field">
            <xsl:attribute name="name">gold_status_flag</xsl:attribute>
			<xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:quality_level">
        <xsl:element name="field">
            <xsl:attribute name="name">quality_level</xsl:attribute>
            <xsl:choose>
            	<xsl:when test=". = ''">0</xsl:when>
            	<xsl:otherwise>
            		<xsl:value-of select="."/>
            	</xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:feedType">
        <xsl:element name="field">
            <xsl:attribute name="name">feed_type</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:lastModifiedBy">
        <xsl:element name="field">
            <xsl:attribute name="name">last_modified_by</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = 'SYSTEM'">Harvester</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:status">
        <xsl:element name="field">
            <xsl:attribute name="name">status</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:searchBaseScore">
        <xsl:element name="field">
            <xsl:attribute name="name">search_base_score</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>


	<xsl:template match="extRif:registryDateModified">
        <xsl:element name="field">
            <xsl:attribute name="name">date_modified</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:reverseLinks">
        <xsl:element name="field">
            <xsl:attribute name="name">reverse_links</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="originatingSource">
        <xsl:element name="field">
            <xsl:attribute name="name">originating_source</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:dataSourceKey">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>   
   
    <xsl:template match="extRif:dataSourceKeyHash">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_key_hash</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:displayTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">display_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:listTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">list_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
   
    <xsl:template match="collection | party | activity | service">
        <xsl:element name="field">
            <xsl:attribute name="name">class</xsl:attribute>
            <xsl:value-of select="local-name()"/>
        </xsl:element>  
        <xsl:element name="field">
            <xsl:attribute name="name">type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>  
        <!--xsl:element name="field">
            <xsl:attribute name="name">date_modified</xsl:attribute>
            <xsl:value-of select="@dateModified"/>
        </xsl:element-->  
        <xsl:apply-templates select="identifier" mode="value"/>
        <xsl:apply-templates select="identifier" mode="type"/>
        <xsl:apply-templates select="name"/>
        
        <xsl:apply-templates select="subject" mode="value"/>
        <xsl:apply-templates select="subject" mode="resolved_value"/>
        <xsl:apply-templates select="subject" mode="type"/>
        <xsl:apply-templates select="subject" mode="vocab_uri"/>
        <xsl:apply-templates select="extRif:broaderSubject" mode="value"/>
        <xsl:apply-templates select="extRif:broaderSubject" mode="resolved_value"/>
        <xsl:apply-templates select="extRif:broaderSubject" mode="type"/>
        <xsl:apply-templates select="extRif:broaderSubject" mode="vocab_uri"/>
        <xsl:apply-templates select="extRif:rights[@licence_group!='']" mode="licence_group"/>        
        <xsl:choose>
        	<xsl:when test="extRif:description">
        	   	<xsl:apply-templates select="extRif:description" mode="value"/>
        		<xsl:apply-templates select="extRif:description" mode="type"/>
        	</xsl:when>
        	<xsl:otherwise>
        	   	<xsl:apply-templates select="description" mode="value"/>
        		<xsl:apply-templates select="description" mode="type"/>
        	</xsl:otherwise>
        </xsl:choose>

        
        <xsl:apply-templates select="displayTitle"/>
        <xsl:apply-templates select="listTitle"/>
        
        <xsl:apply-templates select="location"/>
        <xsl:apply-templates select="coverage"/>
        
        <xsl:apply-templates select="relatedObject"/>
        <xsl:apply-templates select="relatedInfo"/>
    </xsl:template>
    
    <xsl:template match="location">
        <xsl:element name="field">
            <xsl:attribute name="name">location</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedInfo">
    <xsl:element name="field">
        <xsl:attribute name="name">related_info</xsl:attribute>
        <xsl:apply-templates/>
    </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedInfo/*">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template>
    
    <!-- 
    
    <relatedObject>
    <key>fa2e8fcd8be5337d3b1a64c9af2de5f197920d2a</key>
    <relation extRif:type="Point of contact" type="pointOfContact"/>
    <extRif:relatedObjectClass>party</extRif:relatedObjectClass>
    <extRif:relatedObjectType>group</extRif:relatedObjectType>
    <extRif:relatedObjectListTitle> CSIRO Division of Marine and Atmospheric Research - Hobart </extRif:relatedObjectListTitle>
    <extRif:relatedObjectDisplayTitle>CSIRO Division of Marine and Atmospheric Research - Hobart
    </extRif:relatedObjectDisplayTitle>
    </relatedObject>
    
    -->
    <xsl:template match="relatedObject">

            <xsl:apply-templates/>
       
    </xsl:template>
    
    <xsl:template match="relatedObject/key">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedObject/extRif:relatedObjectClass">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_class</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedObject/extRif:relatedObjectType">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_type</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedObject/extRif:relatedObjectListTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_list_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="relatedObject/extRif:relatedObjectDisplayTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_display_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
        
    <xsl:template match="relatedObject/relation">
    <xsl:element name="field">
        <xsl:attribute name="name">related_object_relation</xsl:attribute>
        <xsl:choose>
        	<xsl:when test="@extRif:type">
        		 <xsl:value-of select="@extRif:type"/>
        	</xsl:when>
        	<xsl:otherwise>
        		<xsl:value-of select="@type"/>
        	</xsl:otherwise>
        </xsl:choose>       
    </xsl:element>  
    </xsl:template>

    
    <xsl:template match="coverage/temporal/extRif:date[@type = 'dateFrom'] | coverage/temporal/extRif:date[@type = 'dateTo']">

	        <xsl:element name="field">	            

				<xsl:if test="@type = 'dateFrom'">
					<xsl:attribute name="name">date_from</xsl:attribute>
				</xsl:if>
				<xsl:if test="@type = 'dateTo'">
					<xsl:attribute name="name">date_to</xsl:attribute>
				</xsl:if>
	            <xsl:value-of select="."/>           
	        </xsl:element>     

    </xsl:template>
    
    <xsl:template match="address | electronic | physical | coverage | temporal | extRif:spatial">
            <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="electronic/value | addressPart | location/spatial[@type = 'text']">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template>
    
    <xsl:template match="identifier" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="identifier" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>       
    </xsl:template>
      
    <xsl:template match="subject" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_value_unresolved</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="subject" mode="resolved_value">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_value_resolved</xsl:attribute>
            <xsl:value-of select="@extRif:resolvedValue"/>
        </xsl:element>       
    </xsl:template>
       
    <xsl:template match="subject" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="subject" mode="vocab_uri">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_vocab_uri</xsl:attribute>
            <xsl:value-of select="@extRif:vocabUri"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:broaderSubject" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">broader_subject_value_unresolved</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:broaderSubject" mode="resolved_value">
        <xsl:element name="field">
            <xsl:attribute name="name">broader_subject_value_resolved</xsl:attribute>
            <xsl:value-of select="@extRif:resolvedValue"/>
        </xsl:element>       
    </xsl:template>
       
    <xsl:template match="extRif:broaderSubject" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">broader_subject_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:broaderSubject" mode="vocab_uri">
        <xsl:element name="field">
            <xsl:attribute name="name">broader_subject_vocab_uri</xsl:attribute>
            <xsl:value-of select="@extRif:vocabUri"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:extendedMetadata/extRif:spatialGeometry/extRif:geometry">
    	<xsl:element name="field">
            <xsl:attribute name="name">spatial</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    <xsl:template match="extRif:description | description" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">description_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:description | description" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">description_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    <!-- ignore list -->
    <xsl:template match="location/extRif:spatial/extRif:coords | coverage/extRif:spatial/extRif:coords">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="location/extRif:spatial/extRif:center | coverage/extRif:spatial/extRif:center">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_center</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
     <xsl:template match="extRif:rights[@licence_group!='']" mode="licence_group">
        <xsl:element name="field">
            <xsl:attribute name="name">licence_group</xsl:attribute>
            <xsl:value-of select="@licence_group"/>
        </xsl:element>
    </xsl:template> 


    <xsl:template match="date | description | spatial | text"/>

    
    <xsl:template match="name"/>
   		
   
</xsl:stylesheet>

