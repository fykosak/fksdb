<?xml version="1.0" encoding="utf-8"?>
<stylesheet xmlns="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <output method="text" indent="no"/>
    <strip-space elements="*"/>
    
    <param name="version" select="'1'"/>
    <param name="event" />
    <param name="year"/>
    <param name="date"/>
    <param name="errors-to"/>
    <param name="max-rank"/>
    <param name="max-points"/>


    <template match="/">
        <!-- this allows nesting stats element arbitrarily -->
        <apply-templates select="//export"/>
    </template>
	
    <template match="export">
        <text>version&#x9;</text>
        <value-of select="$version"/>
        <text>&#10;</text>
        <!-- -->
        <text>event&#x9;</text>
        <value-of select="$event"/>
        <text>&#10;</text>
        <!-- -->
        <text>year&#x9;</text>
        <value-of select="$year"/>
        <text>&#10;</text>
        <!-- -->
        <if test="$date">
            <text>date&#x9;</text>
            <value-of select="$date"/>
            <text>&#10;</text>
        </if>
        <!-- -->
        <if test="$errors-to">
            <text>errors-to&#x9;</text>
            <value-of select="$errors-to"/>
            <text>&#10;</text>
        </if>
        <!-- -->
        <if test="$max-rank">
            <text>max-rank&#x9;</text>
            <value-of select="$max-rank"/>
            <text>&#10;</text>
        </if>
        <!-- -->
        <if test="$max-points">
            <text>max-points&#x9;</text>
            <value-of select="$max-points"/>
            <text>&#10;</text>
        </if>
        <!-- -->
        <text>&#10;</text>
        <apply-templates select="column-definitions/column-definition"/>
        <text>&#10;</text>
        <apply-templates select="data/row"/>
    </template>

    <template match="column-definition">
        <if test="position()!=1">
            <text>&#x9;</text>
        </if>
        <value-of select="@name"/>
    </template>

    <template match="row">
        <apply-templates select="col"/>
        <text>&#10;</text>
    </template>

    <template match="col">
        <if test="position()!=1">
            <text>&#x9;</text>
        </if>
        <value-of select="text()"/>
    </template>
</stylesheet>

