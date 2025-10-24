DROP TABLE application;
DROP TABLE experience;
DROP TABLE skill;
DROP TABLE job_circular;
DROP TABLE company_admin;
DROP TABLE company;
DROP TABLE users;




CREATE TABLE company (
  c_id NUMBER(2) PRIMARY KEY,
  name VARCHAR2(20),
  description VARCHAR2(100),
  location VARCHAR2(20)
);

CREATE TABLE company_admin (
  admin_id NUMBER(2) PRIMARY KEY,
  name VARCHAR2(20),
  email VARCHAR2(50),
  password VARCHAR2(32),
  c_id NUMBER(2),
  FOREIGN KEY (c_id) REFERENCES company(c_id) on delete set null
);

CREATE TABLE job_circular (
  j_id NUMBER(2) PRIMARY KEY,
  title VARCHAR2(20),
  description VARCHAR2(100),
  deadline DATE,
  c_id NUMBER(2),
  FOREIGN KEY (c_id) REFERENCES company(c_id) on delete set null
);

CREATE TABLE users (
  u_id NUMBER(2) PRIMARY KEY,
  name VARCHAR2(20),
  email VARCHAR2(50),
  password VARCHAR2(32),
  summary VARCHAR2(100)
);

CREATE TABLE skill (
  s_id NUMBER(2) PRIMARY KEY,
  name VARCHAR2(20),
  u_id NUMBER(2),
  FOREIGN KEY (u_id) REFERENCES users(u_id) on delete cascade
);

CREATE TABLE experience (
  e_id NUMBER(2) PRIMARY KEY,
  u_id NUMBER(2),
  role VARCHAR2(20),
  c_id NUMBER(2),
  FOREIGN KEY (u_id) REFERENCES users(u_id) on delete cascade,
  FOREIGN KEY (c_id) REFERENCES company(c_id) on delete set null
);

CREATE TABLE application (
  a_id NUMBER(2) PRIMARY KEY,
  j_id NUMBER(2),
  u_id NUMBER(2),
  FOREIGN KEY (u_id) REFERENCES users(u_id) on delete set null,
  FOREIGN KEY (j_id) REFERENCES job_circular(j_id) on delete set null
);
