INSERT INTO {SQL_TABLE_PREFIX}forum (id, cat_id,name,date_created,max_attach_size,view_order) VALUES(1, 1,'TestForum',0,1024,1);
INSERT INTO {SQL_TABLE_PREFIX}group_resources(group_id, resource_id) VALUES(3, 1);
INSERT INTO {SQL_TABLE_PREFIX}group_members (user_id, group_id,  up_VISIBLE, up_READ) VALUES (0, 3, 'Y', 'Y');
INSERT INTO {SQL_TABLE_PREFIX}group_members (user_id, group_id, up_VISIBLE, up_READ, up_POST, up_REPLY, up_POLL, up_FILE, up_VOTE, up_RATE, up_SML, up_IMG) VALUES (2147483647, 3, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
INSERT INTO {SQL_TABLE_PREFIX}group_cache (user_id, resource_id, group_id, p_VISIBLE, p_READ) VALUES (0, 1, 3, 'Y', 'Y');
INSERT INTO {SQL_TABLE_PREFIX}group_cache (user_id, resource_id, group_id, p_VISIBLE, p_READ, p_POST, p_REPLY, p_POLL, p_FILE, p_VOTE, p_RATE, p_SML, p_IMG) VALUES (2147483647, 1, 3, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
SELECT setval('{SQL_TABLE_PREFIX}forum_id_seq', 1);
