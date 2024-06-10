# Utilities, Helpers, Convinience functions ....

## coso\\*It
       ConvertIt
    *  EnumIt
       ExportIt:
    *  FormatIt:
       ImportIt:
    *D InputIt: Clean user input, $_* or input::
    *  HtmlIt: Checked/Selected, Array to: options, tags, ul/ol/dlm, tree
    SQL
        *D SQLIt: Query/Where writer helper
          SQLCounter:
          SQLTree:
          JSONER
    Util
        Date
            *D DatePeriodIt
        Array
        Phrases
    Report
        *  TableIt:
        *D TotalIt: Totals & Sub totals from arrays
    *D RoundIt

## itNormalizeSql
>>> Replace ? with values to be binded	
   - $stmt = preg_replace_callback("/(?<!')\?(?!')/muS", function($matches) use (&$values) { return strit(array_shift($values)); }, $stmt);

## Mermaide diagramers


https://dev.to/surgbc/using-github-and-mermaidjs-to-document-software-architecture-using-c4-model-57fn?ref=lukemerrett.com

	classDef borderless stroke-width:0px
	classDef darkBlue fill:#00008B, color:#fff
	classDef brightBlue fill:#6082B6, color:#fff
	classDef gray fill:#62524F, color:#fff
	classDef gray2 fill:#4F625B, color:#fff

	subgraph Legend[Legend]
		Legend1[person]
		Legend2[system]
		Legend3[external person]
		Legend4[external system]
	end
	class Legend1 darkBlue
	class Legend2 brightBlue
	class Legend3 gray
	class Legend4 gray2
id1>"This is the text in the box"]
eight RFC 5424 levels (debug, info, notice, warning, error, critical, alert, emergency).

@startuml
interface Movable {
+ move()
}

class Car <|.. Movable
@enduml
[Child Class Name] -|> [Parent Class Name]
@startuml
abstract class Animal {
+ {abstract} eat()
}

class Dog -|> Animal
@enduml

## On DDX for databases
DDL (Data Definition language): Alter, Create, Drop, ...
DCL (Data Control Language): Grant, Revoke, ...
TCL (Transaction Control Language): Begin, Commit, Rollback, SavePoint, ...
DML (Data Modification Language): Insert, Update, Delete, Truncate, Replace, ...
DQL (Data Query Language); Select

## Mermaid class diagram for itSql
classDiagram

    class ItPreparedStatemnt{
      #string query
      #mysqli_stmt|false statemnt
      +PreparedStatemnt(string, SqlLog)
      +getQuery() string
      +getStatement() mysqli_stmt|false
    }
    class ItSqlLog {
        +setLogLevel(level)
        +log(level, message, context)
        +getLog(minLevel)
    }
	class itSqlError {
	}
    class ItSql {
        #Array[string:string|int] credentials
        #Array[string:string|int] options
        #Array[string|int,string] runOnConnect
        
        #itSqlLog log
        #mysqli

        +ItSql(credentials, runOnConnect, options, logLevel = 'info')

        +query(string|prepareStatement|array, returnShape, bindArguments, mysqlMode) string|null|array throws <<exception>>itSqlError
        +transaction(array) throws <<exception>>itSqlError
        +prepareStatement(string query) ItPreparedStatemnt throws <<exception>>itSqlError

        +getLastInsertedId() string
        +affected_rows()

        +begin() throws <<exception>>itSqlError
        +commit() throws <<exception>>itSqlError
        +rollback() throws <<exception>>itSqlError

        +mysqli() mysqli|null

        +setLogLevel(level)
        +log(level, message, context)
        +getLog(minLevel)
        
    }