
where cheque_pago_id <=> NULL


'VALUES function' is deprecated and will be removed in a future release. Please use an alias (INSERT INTO ... VALUES (...) AS alias) and replace VALUES(col) in the ON DUPLICATE KEY UPDATE clause with alias.col instead
INSERT INTO foo (bar, baz) VALUES (1,2) ON DUPLICATE KEY UPDATE baz=VALUES(baz);
    INSERT INTO foo (bar, baz) VALUES (1,2) AS new_foo ON DUPLICATE KEY UPDATE baz=new_foo.baz;

    create table tval(a int not null primary key , b int, c int);
    INSERT INTO tval (a,b,c) VALUES (1,3,3),(4,5,6) ,(41,51,71);
    INSERT INTO tval (a,b,c) VALUES (1,3,33),(4,5,66) ,(41,51,77) AS new_values ON DUPLICATE KEY UPDATE c = new_values.c;
    INSERT INTO tval (a,b,c) VALUES (1,2,3),(4,5,6) AS new(m,n,p) ON DUPLICATE KEY UPDATE c = m+n;




CREATE TABLE t1 (
    id INT PRIMARY KEY
);

CREATE TABLE t2 (
    id INT PRIMARY KEY
);

INSERT INTO t1 VALUES (1),(2),(3);
INSERT INTO t2 VALUES (2),(3),(4);

/* MINUS */

    /*
    SELECT id FROM t1
    MINUS -- In Microsoft SQL Server, EXCEPT
    SELECT id FROM t2;
    */

    explain SELECT /* MINUS EMULATOR */ t1.id
    FROM t1 LEFT JOIN t2 ON t1.id = t2.id
    WHERE t2.id IS NULL; -- OR t2.col2 IS NULL,...

    SELECT /* MINUS EMULATOR 2 */ t1.id FROM t1
    WHERE NOT EXISTS (SELECT 1 FROM t2 WHERE t1.id <=> t2.id) -- OR t1.col1 <=> t2.col1


CREATE TABLE mytable(
                        id        INTEGER  NOT NULL PRIMARY KEY
    ,title     VARCHAR(19) NOT NULL
    ,parent_id INTEGER  NOT NULL
);
INSERT INTO mytable(id,title,parent_id) VALUES (1,'Dashboard',0);
INSERT INTO mytable(id,title,parent_id) VALUES (2,'Content',1);
INSERT INTO mytable(id,title,parent_id) VALUES (3,'Modules',33);
INSERT INTO mytable(id,title,parent_id) VALUES (17,'User Modules',3);
INSERT INTO mytable(id,title,parent_id) VALUES (31,'Categories',17);
INSERT INTO mytable(id,title,parent_id) VALUES (32,'Categories',30);
INSERT INTO mytable(id,title,parent_id) VALUES (33,'Categories',32);

with recursive cte as (
    select id, parent_id, 1 lvl from mytable
    union all
    select c.id, t.parent_id, lvl + 1
    from cte c
             inner join mytable t on t.id = c.parent_id
)
select id, group_concat(parent_id order by lvl) all_parents
from cte
group by id


-- https://dev.mysql.com/blog-archive/mysql-8-0-1-recursive-common-table-expressions-in-mysql-ctes-part-four-depth-first-or-breadth-first-traversal-transitive-closure-cycle-avoidance/

    /*
     Tables & Data for the example
    CREATE TABLE tree (person CHAR(20), parent CHAR(20));
    INSERT INTO tree VALUES
        ('Robert I', NULL),
        ('Thurimbert', 'Robert I'),
        ('Robert II', 'Thurimbert'),
        ('Cancor', 'Thurimbert'),
        ('Landrade', 'Thurimbert'),
        ('Ingramm', 'Thurimbert'),
        ('Robert III', 'Robert II'),
        ('Chaudegrand', 'Landrade'),
        ('Ermengarde', 'Ingramm');

    CREATE TABLE rockets
    (origin CHAR(20), destination CHAR(20), trip_time INT);
    INSERT INTO rockets VALUES
        ('Earth', 'Mars', 2),
        ('Mars', 'Jupiter', 3),
        ('Jupiter', 'Saturn', 4);
    INSERT INTO rockets VALUES ('Saturn', 'Earth', 9);
    */

    /*
    Breadth-first ordering means we want to see all children (direct descendants) first,
    grouped together, and only then should their own children appear
    */
    -- Breadth-first sort by level
    WITH RECURSIVE descendants AS
        (
        SELECT person, 1 as level
        FROM tree
        WHERE person='Thurimbert'
        UNION ALL
        SELECT t.person, d.level+1
        FROM descendants d, tree t
        WHERE t.parent=d.person
        )
    SELECT * FROM descendants ORDER BY level;

    /*
        Depth-first ordering means we want to see children grouped immediately under their parent
     */
    -- Depth-first sort by path
    WITH RECURSIVE descendants AS
                       (
                           SELECT person, CAST(person AS CHAR(500)) AS path
                           FROM tree
                           WHERE person='Thurimbert'
                           UNION ALL
                           SELECT t.person, CONCAT(d.path, ',', t.person)
                           FROM descendants d, tree t
                           WHERE t.parent=d.person
                       )
    SELECT * FROM descendants ORDER BY path;

    -- cycle detection
    WITH RECURSIVE all_destinations AS (
        SELECT destination AS planet, trip_time AS total_time,
               CAST(destination AS CHAR(500)) AS path, 0 AS is_cycle
        FROM rockets
        WHERE origin='Earth'
        UNION ALL
        SELECT r.destination, d.total_time+r.trip_time,
               CONCAT(d.path, ',', r.destination),
               FIND_IN_SET(r.destination, d.path)!=0
        FROM rockets r, all_destinations d
        WHERE r.origin=d.planet
          AND is_cycle=0
    )
    SELECT * FROM all_destinations;
