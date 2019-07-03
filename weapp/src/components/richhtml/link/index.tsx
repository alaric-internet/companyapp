import Taro, { Component } from '@tarojs/taro'
import { Text } from '@tarojs/components'
import './index.scss'

export default class RichLink extends Component {

  static defaultProps = {
    attrs: {},
    nodes: []
  }

  static options = {
    addGlobalClass: true,
  }

  jumpLink(e) {
    let { href } = this.props.attrs
    let link = ''
    //解析url地址
    
    if(!link){
      return Taro.navigateTo({
        url: '/pages/browser/index?url=' + href
      });
    }
  }

  render () {
    const { attrs, nodes } = this.props
    return (
      <Text onClick={this.jumpLink} className={attrs.class} selectable>{nodes[0].text}</Text>
    )
  }
}