<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html" encoding="utf-8"/>

    <xsl:template match="verification">
        <xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
        <html lang="cs-cz">
            <body>
                <xsl:text>Welcome to Find My Friends</xsl:text>
                <br/>
                <xsl:text>Please, confirm your account via link below.</xsl:text>
                <br/>
                <xsl:element name="a">
                    <xsl:attribute name="href">
                        <xsl:text>http://localhost:3000/verification/confirm/</xsl:text>
                        <xsl:value-of select="code"/>
                    </xsl:attribute>
                    <xsl:value-of select="code"/>
                </xsl:element>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>