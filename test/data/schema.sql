DROP TABLE IF EXISTS faith;
CREATE TABLE faith (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  name      TEXT(32) UNIQUE
);

DROP TABLE IF EXISTS person;
CREATE TABLE person (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  name      TEXT(32) NOT NULL CHECK ( length(name) > 0 ),
  age       INTEGER CHECK ( age >= 0 ),
  gender    TEXT(1) NOT NULL CHECK ( gender IN ('M', 'F') ) DEFAULT ('M'),
  is_alive  INTEGER NOT NULL CHECK ( is_alive BETWEEN 0 AND 1 ) DEFAULT (1),

  father_id INTEGER REFERENCES person(id),
  mother_id INTEGER REFERENCES person(id),
  faith_id  INTEGER REFERENCES faith(id)
);

DROP TABLE IF EXISTS wedlock;
CREATE TABLE wedlock (
  husband_id INTEGER REFERENCES person(id),
  wife_id    INTEGER REFERENCES person(id),
  PRIMARY KEY (husband_id, wife_id)
);