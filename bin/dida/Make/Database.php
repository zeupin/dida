<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Make;

use \PDO;
use \Dida\Db\Db;

/**
 * 数据库的常用操作语句
 */
class Database
{
    /**
     * @var \Dida\Db
     */
    protected $db;

    /**
     * @var \Dida\Db\SchemaInfo\SchemaInfo
     */
    protected $schemainfo;

    /**
     * @var string 输出目录
     */
    protected $outputDir;

    /**
     * 初始化
     */
    public function __construct(array $conf)
    {
        $this->db = new Db($conf);
        $this->db->init();
        $this->schemainfo = $this->db->schemainfo();
    }

    /**
     * 设置输出目录
     */
    public function setOutputDir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            throw new \Exception();
        }
        $this->outputDir = realpath($dir);
    }

    /**
     * 开始批量生成
     */
    public function start()
    {
        $info = $this->schemainfo;

        // 获取数据库列表
        $schemas = $info->getSchemas();

        foreach ($schemas as $schema => $schemainfo) {
            // 获取数据表列表
            $tables = $info->getTables($schema);

            foreach ($tables as $table => $tableinfo) {
                $this->outputTableColumns($schema, $table);
                $this->outputSelect($schema, $table);
                $this->outputUpdate1($schema, $table);
                $this->outputUpdate2($schema, $table);
                $this->outputDelete1($schema, $table);
                $this->outputDelete2($schema, $table);
                $this->outputInsert1($schema, $table);
                $this->outputInsert2($schema, $table);
            }
        }
    }

    /**
     * 输出 ColumnList
     */
    public function outputTableColumns($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        // 字段列表
        $columnlist = array_keys($columns);
        foreach ($columnlist as &$column) {
            $column = "'$column'";
        }
        $columnlist = implode(', ', $columnlist);

        // 生成字段列表
        $file = "$schema.$table.columnlist.php";
        $path = $this->outputDir . DS . $file;
        $content = <<<TEXT
<?php
\${$table}_cols = [$columnlist];

TEXT;

        // 输出
        file_put_contents($path, $content);
    }

    /**
     * 输出 SELECT
     */
    public function outputSelect($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "SELECT";

        $fields = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column`";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;

        $s[] = "FROM";
        $s[] = "    `$table`";
        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.SELECT.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputUpdate1($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "UPDATE";
        $s[] = "    `$table`";
        $s[] = "SET";

        $fields = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column` = ?";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;

        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.UPDATE1.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputUpdate2($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "UPDATE";
        $s[] = "    `$table`";
        $s[] = "SET";

        $fields = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column` = :$column";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;

        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.UPDATE2.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputDelete1($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "DELETE FROM";
        $s[] = "    `$table`";
        $s[] = "WHERE";

        $fields = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column` = ?";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;

        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.DELETE1.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputDelete2($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "DELETE FROM `$table`";
        $s[] = "WHERE";

        $fields = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column` = :$column";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;

        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.DELETE2.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputInsert1($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "INSERT INTO `$table`";
        $s[] = "    (";

        $fields = [];
        $params = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column`";
            $params[] = "    ?";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;
        $s[] = '    )';
        $s[] = "VALUES";
        $s[] = "    (";
        $params = implode(",\n", $params);
        $s[] = $params;
        $s[] = '    )';
        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.INSERT1.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }

    public function outputInsert2($schema, $table)
    {
        $info = $this->schemainfo;

        // 获取字段列表
        $columns = $info->getColumns($schema, $table);

        $s = [];
        $s[] = "INSERT INTO";
        $s[] = "    `$table`";
        $s[] = "    (";

        $fields = [];
        $params = [];
        foreach ($columns as $column => $columninfo) {
            $fields[] = "    `$table`.`$column`";
            $params[] = "    :$column";
        }
        $fields = implode(",\n", $fields);
        $s[] = $fields;
        $s[] = '    )';
        $s[] = "VALUES";
        $s[] = "    (";
        $params = implode(",\n", $params);
        $s[] = $params;
        $s[] = '    )';
        $s[] = ';';
        $s = implode("\n", $s);

        $file = "$schema.$table.INSERT2.sql";
        $path = $this->outputDir . DS . $file;
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $s);
        echo "$path\n";
    }
}
