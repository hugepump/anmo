<template>
    <div class="lb-goods-list">
        <top-nav />
        <div class="page-main">
          <lb-button size='medium' type='success' opType='add'>添加商品</lb-button>
          <div class="form-search">
            <el-form :inline="true" :model="formRules">
              <el-form-item label="部门">
                <el-select v-model="formRules.status" placeholder="请选择">
                  <el-option label="全部" value="0"></el-option>
                  <el-option label="已上架" value="1"></el-option>
                  <el-option label="已售完" value="2"></el-option>
                  <el-option label="为上架" value="3"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="分类">
                <el-select v-model="formRules.type" placeholder="请选择">
                  <el-option label="全部" value="0"></el-option>
                  <el-option label="分类1" value="1"></el-option>
                  <el-option label="分类2" value="2"></el-option>
                  <el-option label="分类3" value="3"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="搜索">
                <el-input v-model="formRules.goods" placeholder="请输入商品名称"></el-input>
              </el-form-item>
            </el-form>
          </div>
          <el-table
            border
            ref="multipleTable"
            :data="tableData"
            tooltip-effect="dark"
            style="width: 100%"
            @selection-change="handleSelectionChange">
            <el-table-column
              type="selection"
              label="全选"
              width="55">
            </el-table-column>
            <el-table-column
              prop="date"
              label="日期"
              width="180">
            </el-table-column>
            <el-table-column
              prop="name"
              label="姓名"
              width="180">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
          </el-table>
          <lb-page
          :page='page'
          :pageSize='pageSize'
          :total='total'
          :selected='multipleSelection.length'
          @handleSizeChange='handleSizeChange'
          @handleCurrentChange='handleCurrentChange'
          >
            <lb-button>批量下架</lb-button>
            <lb-button>批量上架</lb-button>
            <lb-button>批量删除</lb-button>
          </lb-page>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      formRules: {
        status: '',
        type: '',
        goods: ''
      },
      tableData: [{
        date: '2016-05-02',
        name: '王小虎',
        address: '上海市普陀区金沙江路 1518 弄'
      }, {
        date: '2016-05-04',
        name: '王小虎',
        address: '上海市普陀区金沙江路 1517 弄'
      }, {
        date: '2016-05-01',
        name: '王小虎',
        address: '上海市普陀区金沙江路 1519 弄'
      }, {
        date: '2016-05-03',
        name: '王小虎',
        address: '上海市普陀区金沙江路 1516 弄'
      }],
      page: 1,
      pageSize: 10,
      total: 0,
      multipleSelection: []
    }
  },
  methods: {
    toggleSelection (rows) {
      if (rows) {
        rows.forEach(row => {
          this.$refs.multipleTable.toggleRowSelection(row)
        })
      } else {
        this.$refs.multipleTable.clearSelection()
      }
    },
    handleSelectionChange (val) {
      this.multipleSelection = val
    },
    handleSizeChange (val) {
      this.pageSize = val
    },
    handleCurrentChange (val) {
      this.page = val
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-goods-list{
    width: 100%;
    .page-main{
      width: 100%;
      padding: 20px;
      .form-search{
        width: 100%;
        height: 104px;
        background: #F4F4F4;
        margin-top: 30px;
        display: flex;
        align-items: center;
        padding-left: 30px;
        margin-bottom: 20px;
        .el-form{
          margin-top: 10px;
        }
      }
    }
  }
</style>
