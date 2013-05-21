<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:param name="removeFormAttributes" select="'true'"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/|comment()|processing-instruction()">
        <xsl:copy>
          <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="*">
        <xsl:element name="{local-name()}">
          <xsl:apply-templates select="@*|node()"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:attribute name="{local-name()}">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@xml:lang">
        <xsl:attribute name="xml:lang">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@xml:lang">
        <xsl:attribute name="xml:lang">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@schemaLocation | @field_id | @tab_id | @roclass">
        <xsl:if test="$removeFormAttributes != 'true'">
                <xsl:copy-of select="."/>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="dates">
        <xsl:choose>
            <xsl:when test="date[text() != '' or @type != '']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | date[text() != '' or @type != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="citationInfo">
        <xsl:choose>
            <xsl:when test="fullCitation[@style != '' or text() != ''] or citationMetadata[identifier/@type !='' or identifier/text() != '' or title/text() != '' or publisher/text() != ''or context/text() != '' or contributor/@seq !='']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="citationMetadata">
        <xsl:choose>
            <xsl:when test="identifier/@type !='' or identifier/text() != '' or title/text() != '' or publisher/text() != ''or context/text() != '' or contributor/@seq !='' or contributor/namePart/text() != ''">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="relatedInfo">
        <xsl:choose>
            <xsl:when test="identifier/@type !='' or identifier/text() != '' or @type != '' or format/identifier != '' or format/identifier/@type != '' or title/text() !='' or notes/text() != ''">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="format">
        <xsl:choose>
            <xsl:when test="identifier[@type != '' or text() != '']">
                <xsl:copy>
                    <xsl:apply-templates select="identifier[@type != '' or text() != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="location">
        <xsl:choose>
            <xsl:when test="(@dateFrom!= '') or (@dateTo!= '') or (address/electronic/value/text()) or (address/electronic/@type != '')  or (address/electronic/arg) or (address/physical/@type != '' ) or (address/physical/addresspart) or (address/physical/addresspart/@type != '') or (spatial/text()) or (spatial/@type != '')">
                <xsl:copy>
                    <xsl:apply-templates select="@dateFrom | @dateTo | address | spatial" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="coverage">
        <xsl:choose>
            <xsl:when test="(temporal/date/text()) or (temporal/date/@dateFormat) or (temporal/date/@type != '') or (temporal/text/text()) or (spatial/text()) or (spatial/@type != '')">
                <xsl:copy>
                    <xsl:apply-templates select="temporal | spatial" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="address">
        <xsl:choose>
            <xsl:when test="(electronic/value/text()) or (electronic/@type != '')  or (electronic/arg) or (physical/@type != '' ) or (physical/addresspart) or (physical/addresspart/@type != '')">
                <xsl:copy>
                    <xsl:apply-templates select="electronic | physical" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>


    <xsl:template match="temporal">
        <xsl:choose>
            <xsl:when test="(date/text()) or (date/@dateFormat) or (date/@type != '') or (text/text())">
                <xsl:copy>
                    <xsl:apply-templates select="date" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="physical">
        <xsl:choose>
            <xsl:when test="@type != '' or addressPart[@type != '' or text() != '']">
                <xsl:copy>
                    <xsl:apply-templates select="@* | addressPart[@type != '' or text() != '']" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="electronic">
        <xsl:choose>
            <xsl:when test="@type != '' or value/text() != ''">
                <xsl:copy>
                    <xsl:apply-templates select="@* | node()" />
                </xsl:copy>   
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="relatedObject[not(key/text()) and relation/@type = '' and not(relation/description/text()) and not(relation/url/text())]"/><xsl:template match="description[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="spatial[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="text[not(text())]"/>
    <xsl:template match="addressPart[not(text()) and (not(@type) or @type='')]"/>
    <xsl:template match="subject[(not(@type) or @type='') and not(text())]"/>
    <xsl:template match="namePart[(not(@type) or @type='') and not(text()) and (following-sibling::namePart[text() != ''] or preceding-sibling::namePart[text() != ''])]"/>
    <xsl:template match="date[not(parent::citationMetadata) and not(text()) and not(@dateFormat or @dateFormat = '') and (not(@type) or @type='')]"/>
    <xsl:template match="fullCitation[(not(@style) or @style='') and not(text()) ]"/>
    <xsl:template match="identifier[not(parent::citationMetadata) and not(parent::relatedInfo) and not(text()) and (not(@type) or @type='')]"/>
    <xsl:template match="citationMetadata[(not(identifier/@type) or identifier/@type='') and not(identifier/text()) and not(title/text()) and not(publisher/text()) and not(context/text()) and not(contributor/@seq) and not(contributor/namePart/text())]"/>

   
</xsl:stylesheet>

