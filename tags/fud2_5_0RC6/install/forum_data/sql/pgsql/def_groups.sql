INSERT INTO {SQL_TABLE_PREFIX}groups (id, name, inherit_id, joinmode, forum_id, p_VISIBLE, p_READ) values(1, 'Global Anonymous Access', 0, 'NONE', 0, 'Y', 'Y');
INSERT INTO {SQL_TABLE_PREFIX}groups (id, name, inherit_id, joinmode, forum_id, p_VISIBLE, p_READ, p_POST, p_REPLY, p_POLL, p_FILE, p_VOTE, p_RATE, p_SML, p_IMG) values(2, 'Global Registered Access', 0, 'NONE', 0, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
INSERT INTO {SQL_TABLE_PREFIX}groups (id, name, forum_id, p_VISIBLE, p_READ, p_POST, p_REPLY, p_EDIT, p_DEL, p_STICKY, p_POLL, p_FILE, p_VOTE, p_RATE, p_SPLIT, p_LOCK, p_MOVE, p_SML, p_IMG) VALUES (3, 'TestForum', 1,  'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
SELECT setval('{SQL_TABLE_PREFIX}groups_id_seq', 3);
