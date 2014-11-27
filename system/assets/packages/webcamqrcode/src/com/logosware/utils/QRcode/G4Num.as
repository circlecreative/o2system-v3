/**************************************************************************
* LOGOSWARE Class Library.
*
* Copyright 2009 (c) LOGOSWARE (http://www.logosware.com) All rights reserved.
*
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the Free Software
* Foundation; either version 2 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with
* this program; if not, write to the Free Software Foundation, Inc., 59 Temple
* Place, Suite 330, Boston, MA 02111-1307 USA
*
**************************************************************************/ 
package com.logosware.utils.QRcode
{
	import com.logosware.utils.QRcode.GFstatic;
	/**
	 * GF(2^4)を扱うためのクラス
	 **/
	public class G4Num {
		private var _vector:uint;
		private var _power:int;
		/**
		 * コンストラクタ
		 * @param power 指数
		 **/
		public function G4Num(power:int) {
			setPower( power );
		}
		/**
		 * 指数を指定する
		 * @param power 指数
		 **/
		public function setPower( power:int ):void {
			_power = power;
			if ( _power < 0 ) {
				_vector = 0;
			} else {
				_power %= 15;
				_vector = GFstatic._power2vector_4[_power];
			}
		}
		/**
		 * 整数値を指定する
		 * @param vector 整数値
		 **/
		public function setVector( vector:uint ):void {
			_vector = vector;
			_power = GFstatic._vector2power_4[_vector];
		}
		/**
		 * 整数値を取得する
		 * @param 整数値
		 **/
		public function getVector():uint {
			return _vector;
		}
		/**
		 * 指数を取得する
		 * @param 指数
		 **/
		public function getPower():int {
			return _power;
		}
		/**
		 * 足し算を行う。整数値同士のxorを取る。
		 * @param other 足す対象となるG4Numインスタンス
		 * @param 計算結果
		 **/
		public function plus( other:G4Num ):G4Num {
			var newVector:uint = _vector ^ other.getVector();
			return new G4Num( GFstatic._vector2power_4[ newVector ] );
		}
		/**
		 * 乗算を行う。指数同士の足し算を行う。
		 * @param other かける対象となるG4Numインスタンス
		 * @param 計算結果
		 **/
		public function multiply( other:G4Num ):G4Num {
			if ( (_power == -1) || (other.getPower() == -1 ) ) {
				return new G4Num( -1 );
			} else {
				return new G4Num( _power + other.getPower() );
			}
		}
	}
}