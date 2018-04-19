import React, { Component } from 'react';
import {connect} from 'react-redux';
import { bindActionCreators } from 'redux';
import { Route } from 'react-router-dom';
import * as facadeActions from '../../actions/facadeActions';
import Button from '../../components/button/button';
import Switcher from '../../components/switcher/switcher';
import List from '../../components/list/list';
import ColorPicker from '../../components/color-picker/color-picker';
import facadeColors from '../../data/facade-colors';
import soffitsColors from '../../data/soffits-colors';

class Facade extends Component {
	constructor(props) {
		super(props);
		
		this.facadeColors = facadeColors;
		this.soffitsColors = soffitsColors;
		this.facadeLabels = [];
		this.facadeLabels['saiding'] = 'сайдинг';
		this.facadeLabels['plaster'] = 'штукатурка';
	}
	
	render() {
		//const { type, color } = this.props.roof;
		
		return <div>
			<Route exact path='/' render={() =>
				<div className='button__wrapper'>
					<Button label='Фасад' modifiers={['w80']} link='/facade/'></Button>
				</div>
				} />

			<Route path='/facade/' render={() => <div>
					<div className='button__wrapper'>
						<Button label='Назад' modifiers={['w80']} link='/'></Button>
					</div>
				</div>
				} />

			<Route path='/facade/' render={() => <div>
					<div className='button__wrapper'>
						<Switcher labels={['сайдинг', 'штукатурка']} links={['/facade/saiding/', '/facade/plaster/']}
						modifiers={['w80']} 
						setAction={this.props.facadeActions.facadePickupType}
						codes={['saiding', 'plaster']}></Switcher>											
					</div>
					<div className='button__wrapper'>
						<Button activeClass='button_active' label='софитный сайдинг' modifiers={[]} link='/facade/saiding/soffits/'></Button>
					</div>
				</div>
				} />

			<Route exact path='/facade/saiding/' render={() => <div>
				<ColorPicker colors={this.facadeColors} currentColor={this.props.facade.color} setColor={this.props.facadeActions.facadePickupColor}></ColorPicker>
			</div>
			} />

			<Route exact path='/facade/saiding/soffits/' render={() => <div>
				<ColorPicker colors={this.soffitsColors} currentColor={this.props.soffits.color} setColor={this.props.facadeActions.soffitsPickupColor}></ColorPicker>
			</div>
			} />


			<Route exact path='/facade/plaster/' render={() => <div>
				<ColorPicker colors={this.facadeColors} currentColor={this.props.facade.color} setColor={this.props.facadeActions.facadePickupColor}></ColorPicker>
			</div>
			} />

			<Route exact path='/facade/plaster/soffits/' render={() => <div>
				<ColorPicker colors={this.soffitsColors} currentColor={this.props.soffits.color} setColor={this.props.facadeActions.soffitsPickupColor}></ColorPicker>
			</div>
			} />

		</div>;
	}
}

function mapStateToProps(state) {
	return {
		facade: state.facade,
		soffits: state.soffits
	}
}

function mapDispatchToProps(dispatch) {
	return {
		facadeActions: bindActionCreators(facadeActions, dispatch)
	}
}

export default connect(mapStateToProps, mapDispatchToProps)(Facade);
