<template>
    <div class="lb-card-manage">
        <div class="lb-form">
            <el-form :inline="true" :model="formInline">
              <el-form-item label="部门">
                <el-select v-model="formInline.region" placeholder="请选择部门">
                  <el-option label="开发部" value="kf"></el-option>
                  <el-option label="市场部" value="sc"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="搜索">
                <el-input v-model="formInline.user" placeholder="搜索员工"></el-input>
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
            <lb-button>设置默认名片</lb-button>
        </lb-page>
      </div>
</template>

<script>
export default {
  props: ['showIndex'],
  data () {
    return {
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
      formInline: {
        user: '',
        region: ''
      },
      page: 1,
      pageSize: 10,
      total: 0,
      multipleSelection: []
    }
  },
  created () {
    console.log('名片管理')
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
    .lb-card-manage{
      width: 100%;
      padding: 0 24px;
      .lb-form{
        margin: 32px 0;
        width: 100%;
        height: 104px;
        background: #F4F4F4;
        display: flex;
        align-items: center;
        padding-left: 50px;
        .el-form-item{
          white-space: nowrap;
        }
      }
      .el-table .thead{
        color: #909399;
      }
      .bot{
        width: 100%;
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        height: 40px;
        font-size: 14px;
        color: #101010;
        >div{
          display: flex;
          align-items: center;
          white-space: nowrap;
          >div{
            &:first-child{
              height: 40px;
              padding-right: 30px;
              margin-right: 30px;
              border-right: 1px solid #E8E8E8;
              line-height: 40px;
            }
            .el-button{
              margin-left: 10px;
            }
          }
        }
      }
    }
</style>
