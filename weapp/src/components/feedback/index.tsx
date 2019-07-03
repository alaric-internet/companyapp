import Taro, { Component } from '@tarojs/taro'
import { View, Text, RadioGroup, Label, Radio, Input, Button, Form } from '@tarojs/components'
import './index.scss'
import fetch from '../../request'

export default class Feedback extends Component {
  static defaultProps = {

  }

  state = {
    questions: [
      {
        title: '1.您的学历层次是？',
        field: 'xueli',
        list: [
          { id: 1, name: '高中' },
          { id: 2, name: '中专' },
          { id: 3, name: '大专' },
          { id: 4, name: '本科' },
          { id: 5, name: '其他' },
        ]
      }, {
        title: '1.您目前年龄？',
        field: 'nianling',
        list: [
          { id: 1, name: '18周岁以下' },
          { id: 2, name: '18-25周岁' },
          { id: 3, name: '26-30周岁' },
          { id: 4, name: '30周岁以上' },
        ]
      }, {
        title: '1.您所学的专业？',
        field: 'zhuanye',
        list: [
          { id: 1, name: '经济管理类' },
          { id: 2, name: '计算机类' },
          { id: 3, name: '建筑工程类' },
          { id: 4, name: '医疗食品类' },
          { id: 5, name: '其他' },
        ]
      }, {
        title: '1.您目前工作年限？',
        field: 'nianxian',
        list: [
          { id: 1, name: '1-3年' },
          { id: 2, name: '3-6年' },
          { id: 3, name: '6-8年' },
          { id: 4, name: '8-10年' },
          { id: 5, name: '其他' },
        ]
      },
    ]
  }

  static options = {
    addGlobalClass: true,
  }

  submit = (e) => {
    console.log(e)
    let data = e.detail.value
    if (!(/^1[3456789]\d{9}$/.test(data.tel))) {
      return Taro.showToast({
        icon: 'none',
        title: '请填写正确的手机号',
      })
    }
    data.ip = ''
    data.time = ''
    data.dede_fields = 'tel,text;ip,text;time,text;xueli,radio;nianling,radio;zhuanye,radio;nianxian,radio'
    data.dede_fieldshash = 'e12f92e5b3a3b5e552b08d2aba817716'
    data.diyid = 1
    data.do = 2
    //data.action = 'post'
    Taro.request({
      url: 'http://jy.zgbzjd.com/plus/diy.php',
      data: data,
      method: 'POST',
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    }).then((res) => {
      return Taro.showToast({
        icon: 'none',
        title: '恭喜您，申请提交成功，稍后将会有工作人员与您联系，请注意接听电话',
      })
    }).catch((err) => {
      return Taro.showToast({
        icon: 'none',
        title: '恭喜您，申请提交成功，稍后将会有工作人员与您联系，请注意接听电话',
      })
    })
  }

  render() {
    const { questions } = this.state

    return (
      <View className='feedback'>
        <Form onSubmit={this.submit}>
          <Image mode='widthFix' class='feedback-image' src='http://jy.zgbzjd.com/templets/default1/index_files/xfwap01.jpg' />
          {questions.map((item, index) => {
            return <View key={index} className='feedback-item'>
              <View className='question'>{item.title}</View>
              <RadioGroup className='answer' name={item.field}>
                {item.list.map((item2, index2) => {
                  return <Label key={index2} className='answer-label'>
                    <View className="item">
                      <Radio value={item2.id}>&nbsp;&nbsp;{item2.name}</Radio>
                    </View>
                  </Label>
                })}
              </RadioGroup>
            </View>
          })}
          <View className='input-item'>
            <Input className='input-field' name='tel' type='text' placeholder='请输入您的手机号码' />
          </View>
          <View className='submit-item'>
            <Button className='submit-button' size='mini' type='warn' form-type='submit'>立即获取测评结果</Button>
          </View>
          <View className='feedback-tips'>-------目前已有5766人参与测试------</View>
        </Form>
      </View>
    )
  }
}