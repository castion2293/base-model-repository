# 基礎抽象類別供專案使用

## AbstractBaseModel

### 使用方法

```bash
namespace App\Models;

use Pharaoh\BaseModelRepository\Models\AbstractBaseModel;

class Blog extends AbstractBaseModel
{
}
```

### 功能
* 自動載入 HasFactory Trait
* 自動設定 $guard = ['id']
* 自動序列化 DataTime 格式 `Y-m-d H:i:s`

## AbstractBaseRepository

### 使用方法

```bash
namespace App\Repositories;

use App\Models\Blog;
use Pharaoh\BaseModelRepository\Repositories\AbstractBaseRepository;

class BlogRepository extends AbstractBaseRepository
{
    public function __construct(Blog $blog)
    {
        $this->model = $blog;
        $this->table = $this->model->getTable();
    }
}
```

### 取得所有資料
```bash
$repository->getAll($fields, $eagerLoad);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $fields | 要搜尋的欄位 | array | ['name', 'type'] | ['*'] |
| $eagerLoad | 預載入關聯 | array | ['post', 'post.comment'] | [] |

### 依搜尋條件取得資料(whereIn)
```bash
$repository->getByWhereIn($whereField, $whereValue, $field, $eagerLoad);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $whereField | 搜尋條件欄位名稱 | string | 'name' | 'id' |
| $whereValue | 搜尋條件資訊 | array | [1, 2, 3] | [] |
| $field | 要搜尋的欄位 | array | ['id', 'name'] | ['*'] |
| $eagerLoad | 預載入關聯 | array | ['post', 'post.comment'] | [] |

### 取得單筆資料
```bash
$repository->find($id, $eagerLoad);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $id | PK |  | 123 |  |
| $eagerLoad | 預載入關聯 | array | ['post', 'post.comment'] | [] |

### 取得單筆資料(單一搜尋條件)
```bash
$repository->findByWhere($where, $field, $eagerLoad);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $where | 搜尋條件資訊 | array | ['status', '=', 1] | |
| $field | 要搜尋的欄位 | array | ['id', 'name'] | ['*'] |
| $eagerLoad | 預載入關聯 | array | ['post', 'post.comment'] | [] |

完整範例
```bash
$repository->findByWhere([
    ['status', '=', '1'],
    ['subscribed', '<>', '1'],
]);
```
or
```bash
$repository->findByWhere([
    ['status' => '1'],
    ['subscribed' => '1'],
]);
```

### 取得單筆資料 從寫入資料庫取得
```bash
$repository->findWriteConnect($id);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $id | PK |  | 123 |  |

### 取得單筆資料 從寫入資料庫取得 並加排他鎖
```bash
$repository->findWriteConnectByLockForUpdate($id);
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $id | PK |  | 123 |  |

### 尋找在某個欄位有重複的數值及個數
```bash
$repository->findDuplicateValue($field, $where, $extraParameters, $havingCount)
```
| 參數 | 說明 | 類型 | 範例 | 預設 |
| ------------|:----------------------- | :------| :------| :------|
| $field | 要搜尋的欄位 | string | 'member_id' | |
| $where | 搜尋條件資訊 | array  | ['status', '=', 1] | |
| $havingCount | 取多少以上 | int  | 5 | 1 |

完整範例
```bash
$repository->findDuplicateValue('member_id', [
  ['start_at', '>', '2022-01-10 00:00:00'],
  ['end_at', '<', '2022-01-10 23:59:59'],
  ['status', '=', 'OK']
], 1)
```

### 新增資料
```bash
$repository->store($parametes)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------| 
| $parametes | 新增資料陣列 | array |

完整範例
```bash
$repository->store([
  'name' => 'nick',
  'age' => 20
])
```

### 新增多筆資料
```bash
$repository->insertMuti($parametes)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------| 
| $parametes | 新增資料陣列 | array |

完整範例
```bash
$repository->insertMuti([
  [
    'name' => 'nick',
    'age' => 20,
  ],
  [
    'name' => 'john',
    'age' => 20
  ]
])
```

### 更新單筆資料
```bash
$repository->update($id, $parameters)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------| 
| $id | PK |  |
| $parametes | 更新資料陣列 | array |

完整範例
```bash
$repository->update($id, [
  'name' => 'jack',
  'age' => 19
])
```

### 更新單筆資訊 根據指定欄位
```bash
$repository->updateByWhere($where, $parameters)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------| 
| $where | 搜尋條件資訊 | array |
| $parametes | 更新資料陣列 | array |

完整範例
```bash
$repository->updateByWhere(
  [
      'status' => 3,
      'type' => 1,
  ], 
  [
      'status' => 1,
      'updated_at' => now()
  ]
)
```

### 更新多筆資料
```bash
$repository->updateMuti($data, $parameters, $field)
```
| 參數 | 說明 | 類型 | 預設 |
| ------------|:----------------------- | :------| :------| 
| $data | 更新條件資料 | array | |
| $parameters | 更新資料陣列 | array | |
| $field | 更新條件名稱 | string | 'id' |

完整範例
```bash
$repository->updateMuti(
  [1, 2, 3],
  [
      'status' => 1,
      'updated_at' => now()
  ],
  'id'
)
```

### 資料新增，存在則更新
```bash
$repository->updateOrInsert($where, $parameters)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------| 
| $where | 搜尋條件資訊 | array |
| $parametes | 更新資料陣列 | array |

完整範例
```bash
$repository->updateOrInsert(
  [
      'email' => 'john@example.com',
      'name' => 'John'
  ],
  [
      'phone' => '0994930918'
  ]
)
```

### 批次更新 or 新增資料
```bash
$repository->updateOrCreateMulti($parameters)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------|
| $parametes | 更新資料陣列 | array |

完整範例
```bash
$repository->updateOrCreateMulti(
  [
      'email' => 'john@example.com',
      'name' => 'John',
      'phone' => '0994930918'
  ],
  [
      'email' => 'jack@example.com',
      'name' => 'jack',
      'phone' => '0994930920'
  ]
)
```

### 一次更新多筆數據
```bash
$repository->updateMultiRows($setField, $caseField, $setValue)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------|
| $setField | 欲更新的欄位名稱 | string |
| $caseField | 查詢依據欄位名稱 | string |
| $setValue | 愈設定的數值 | array |

完整範例
```bash
$repository->updateMultiRows(
  'score',
  'id',
  [
      1 => 90,
      2 => 80,
      3 => 70
  ]
)
```

### 增加數量
```bash
$repository->increment($where, $field, $num)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------|
| $where | 搜尋條件資訊 | array |
| $field | 愈增加數量的欄位名稱 | string |
| $num | 愈增加的數量 | int |

完整範例
```bash
$repository->increment(
  ['name' => 'john'],
  'amount',
  10
)
```

### 減少數量
```bash
$repository->decrement($where, $field, $num)
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------|
| $where | 搜尋條件資訊 | array |
| $field | 愈減少數量的欄位名稱 | string |
| $num | 愈減少的數量 | int |

完整範例
```bash
$repository->decrement(
  ['name' => 'john'],
  'amount',
  5
)
```

### 依條件刪除資料
```bash
$repository->deleteByWhere($where);
```
| 參數 | 說明 | 類型 | 
| ------------|:----------------------- | :------|
| $where | 搜尋條件資訊 | array |

完整範例
```bash
$repository->deleteByWhere([
  ['name' => 'john'],
  ['age' => 20]
]);
```

## AbstractBaseScope
