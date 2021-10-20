-- ----------------------------
-- Table structure for tb_article
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_article";
CREATE TABLE "public"."tb_article" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "title" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "content" text COLLATE "pg_catalog"."default" NOT NULL,
  "time" timestamp(6) NOT NULL
)
;

-- ----------------------------
-- Records of tb_article
-- ----------------------------

-- ----------------------------
-- Table structure for tb_member
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_member";
CREATE TABLE "public"."tb_member" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "username" varchar(32) COLLATE "pg_catalog"."default" NOT NULL,
  "password" varchar(255) COLLATE "pg_catalog"."default" NOT NULL
)
;
COMMENT ON COLUMN "public"."tb_member"."username" IS '用户名';
COMMENT ON COLUMN "public"."tb_member"."password" IS '密码';

-- ----------------------------
-- Records of tb_member
-- ----------------------------

-- ----------------------------
-- Table structure for tb_performance
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_performance";
CREATE TABLE "public"."tb_performance" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "value" varchar(255) COLLATE "pg_catalog"."default" NOT NULL
)
;

-- ----------------------------
-- Records of tb_performance
-- ----------------------------

-- ----------------------------
-- Table structure for tb_test_json
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_test_json";
CREATE TABLE "public"."tb_test_json" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "json_data" json NOT NULL
)
;
COMMENT ON COLUMN "public"."tb_test_json"."json_data" IS 'json数据';
COMMENT ON TABLE "public"."tb_test_json" IS 'test';

-- ----------------------------
-- Records of tb_test_json
-- ----------------------------

-- ----------------------------
-- Table structure for tb_test_soft_delete
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_test_soft_delete";
CREATE TABLE "public"."tb_test_soft_delete" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "title" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "delete_time" int4 NOT NULL DEFAULT 0
)
;

-- ----------------------------
-- Records of tb_test_soft_delete
-- ----------------------------

-- ----------------------------
-- Table structure for tb_tree
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_tree";
CREATE TABLE "public"."tb_tree" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "parent_id" int4 NOT NULL,
  "name" varchar(32) COLLATE "pg_catalog"."default" NOT NULL
)
;

-- ----------------------------
-- Records of tb_tree
-- ----------------------------
INSERT INTO "public"."tb_tree" VALUES (1, 0, 'a');
INSERT INTO "public"."tb_tree" VALUES (2, 0, 'b');
INSERT INTO "public"."tb_tree" VALUES (3, 0, 'c');
INSERT INTO "public"."tb_tree" VALUES (4, 1, 'a-1');
INSERT INTO "public"."tb_tree" VALUES (5, 1, 'a-2');
INSERT INTO "public"."tb_tree" VALUES (6, 4, 'a-1-1');
INSERT INTO "public"."tb_tree" VALUES (7, 4, 'a-1-2');
INSERT INTO "public"."tb_tree" VALUES (8, 2, 'b-1');
INSERT INTO "public"."tb_tree" VALUES (9, 2, 'b-2');

-- ----------------------------
-- Table structure for tb_update_time
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_update_time";
CREATE TABLE "public"."tb_update_time" (
  "id" int4 NOT NULL GENERATED BY DEFAULT AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
),
  "date" date,
  "time" time(6),
  "datetime" timestamp(6),
  "timestamp" timestamp(6),
  "int" int4,
  "bigint" int8
)
;

-- ----------------------------
-- Records of tb_update_time
-- ----------------------------

-- ----------------------------
-- Primary Key structure for table tb_article
-- ----------------------------
ALTER TABLE "public"."tb_article" ADD CONSTRAINT "tb_article_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_member
-- ----------------------------
ALTER TABLE "public"."tb_member" ADD CONSTRAINT "tb_member_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_performance
-- ----------------------------
ALTER TABLE "public"."tb_performance" ADD CONSTRAINT "tb_performance_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_test_json
-- ----------------------------
ALTER TABLE "public"."tb_test_json" ADD CONSTRAINT "tb_test_json_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_test_soft_delete
-- ----------------------------
ALTER TABLE "public"."tb_test_soft_delete" ADD CONSTRAINT "tb_test_soft_delete_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_tree
-- ----------------------------
ALTER TABLE "public"."tb_tree" ADD CONSTRAINT "tb_tree_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tb_update_time
-- ----------------------------
ALTER TABLE "public"."tb_update_time" ADD CONSTRAINT "tb_update_time_pkey" PRIMARY KEY ("id");