# Distribution

统一分销模块，支持多种模式。

本模块依赖 Drupal Commerce 8.x-2.5 或以上版本。

## 重要概念

### `普通用户` 和 `分销用户`

`普通用户` 是指没有 `推广资格` 的用户，他们无法通过推广商品来获取佣金。

`分销用户` 是指具有 `推广资格` 的用户，他们可以通过推广商品来获取佣金，
而且可以发展更多的 `下级分销用户（即下线）`。
`分销用户` 可以通过多种方式来发展下线。

`普通用户` 可以通过多种方式 `转化` 为 `分销用户`。
如果该转化是由某已有的 `分销用户` 的 `发展行为` 所促成的，
那么该新 `分销用户` 将成为其下线。

### 推广行为

`推广行为` 是指 `分销用户` 通过分享商品来促成 `普通用户` 的购买行为。

同一 `普通用户` 可能会接受过来自不同 `分销用户` 的推广，
也就是查看过多个来自不同 `分销用户` 的分享的内容。

如果 `普通用户` 产生了购买行为，那么这些 `推广者` 可以平分一笔 `推广佣金`。

### `分销用户` 之间的上下级关系与 `链级佣金`

`分销用户` 可以通过多种方式来发展下线。

当一个 `分销用户` 或其 `最后推广用户` 产生购买行为时，将产生 `链级佣金`。
该用户的所有上级，将按设定的比例和特定方式分割这笔 `链级佣金`。

### 购买行为

`购买行为` 是指用户购买商品，产生订单的行为。

- `普通用户` 的购买行为。
  
  有两种情况：
  
  - 自主购买
    
    没有接受过 `推广者` 的推广行为，自行主动购买。
    不会产生佣金。
    
  - 推广购买
    
    接受过 `推广者` 的推广行为，推广者将获得 `推广佣金`。
    最后一位 `推广者` 将作为 `1级分佣者`，产生 `链级佣金`。
    
- `分销用户` 的购买行为。

  该 `分销用户` 将作为 `1级分佣者`，产生 `链级佣金`。
  但该 `分销用户`的佣金直接体现体商品的价格调整。
  
### `团队` 和 `团队领导`

`分销用户` 可以进一步提升为 `团队领导`，这意味着其所有下线发生购买行为时，
该 `团队领导` 都能获得一定的 `团队佣金`。

`团队领导` 的下线可能也是 `团队领导`，称为 `下线团队领导`。
`下线团队领导` 的下线或者下线的推广用户发生购买行为时，其 `上级团队领导`
不会获取 `团队佣金`。

`分销用户` 提升为 `团队领导` 是有多种途径的。

### `佣金` 的来源

分 `推广佣金`、 `链级佣金`、 `团队佣金` 三种。
它们分别取于商品购买价格中的一定比例，但本质上，它们只是针对特定商品所设定
的三个数字。

### `分销折扣`

`普通用户` 接受推广而发生购买时，商品价格会进行折扣调整，称为 `分销折扣`。
但如果 `普通用户` 是自主购买，则不能享受 `分销折扣`。

`分销用户` 发生购买行为时， 也能享受 `分销折扣`。

`分销折扣` 本质上是针对特定商品设定的一个优惠金额。

## 数据结构

### Content Entity
- [x] distributor 分销用户
- [x] leader  团队领导
- [x] target  分销商品
- [x] level   分佣链级分佣比例设置
- [x] event   分销事件
- [x] commission 分佣项
- [x] promoter  普通用户绑定的推广者（分销用户）

### Simple Config

- enable_commission 启用佣金
  - promotion 启用推广佣金
  - chain     启用链级佣金
  - leader    启用团队领导佣金

- transform 普通用户转化分销用户方式设置
  - auto 是否开启购买商品后自动转化

## 服务

- [x] DistributionManager
  - [ ] 创建 target
  - [ ] 创建 level
  - [x] 创建 distributor
  - [ ] 升级为领导 upgradeToLeader
  - [ ] 创建 promoter
  - [ ] 创建分销事件 distribute （自动创建分佣项，并调用Finance模块服务进行记账）
  - [ ] 取消分销事件 cancelDistribution （取消一个订单的分销佣金）

## 事件处理器

- [x] 订单place时，把用户转化为分销用户 （如果配置启用了自动转化）
- [x] 订单 cancel 时，取消佣金

## 界面

- [x] 设置
- [x] 申请成为分销商接口
- [ ] 分销用户管理列表
- [ ] 分销用户审核

## Commerce promotion 价格调整

### commerce_condition
- [ ] 当订单购买者的角色为 `分销商` 时
- [ ] 当订单是通过分销者推广链接购买时（查到推广者绑定表）

### promotion_offer
- [ ] 针对订单项调整该可购买物的分销优惠价 