# 进件管理API扩展说明

## 接口变更

### 获取进件管理列表接口
- **接口路径**: `GET /api/finance/application/list`
- **请求参数**: 保持不变
- **响应数据格式变更**: 

#### 原响应格式
```json
{
  "code": 20000,
  "message": "success",
  "data": {
    "list": [...],
    "total": 100
  }
}
```

#### 新响应格式
```json
{
  "code": 20000,
  "message": "success",
  "data": {
    "list": [...],
    "total": 100,
    "cityOptions": [
      {
        "label": "厦门",
        "value": "厦门"
      },
      {
        "label": "杭州",
        "value": "杭州"
      },
      {
        "label": "武汉",
        "value": "武汉"
      }
    ],
    "channelOptions": [
      {
        "label": "渠道A",
        "value": "channel_a"
      },
      {
        "label": "渠道B",
        "value": "channel_b"
      }
    ],
    "userOptions": [
      {
        "label": "张三",
        "value": "zhangsan"
      },
      {
        "label": "李四",
        "value": "lisi"
      }
    ]
  }
}
```

## 字段说明

- `cityOptions`: 城市选项列表，用于表单中的城市下拉选择
- `channelOptions`: 渠道选项列表，用于表单中的渠道下拉选择  
- `userOptions`: 用户选项列表，用于表单中的业务员、风控人员、审批人等下拉选择

## 实现建议

后端可通过以下方式实现选项数据的提供：

1. 城市选项：可硬编码或从地区表中获取
2. 渠道选项：从渠道管理表中获取所有有效渠道
3. 用户选项：从用户表中获取具有相应权限的用户列表（如业务员、风控人员等角色）